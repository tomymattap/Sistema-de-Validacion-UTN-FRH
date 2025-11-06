<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require_once 'conexion.php';

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificación del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Error de validación. Por favor, intente de nuevo.";
    }

    $identificador = $_POST['identificador'] ?? '';

    if (!$error && empty($identificador)) {
        $error = "El CUIL o Legajo es obligatorio.";
    } else {
        $user = null;
        $email = null;
        $nombre = null;
        $user_type = '';

        // Buscar en admin
        $stmt_admin = $conexion->prepare("SELECT Legajo, Nombre, Email, Password FROM admin WHERE Legajo = ?");
        $stmt_admin->bind_param("s", $identificador);
        $stmt_admin->execute();
        $result = $stmt_admin->get_result();
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $email = $user['Email'];
            $nombre = $user['Nombre'];
            $user_type = 'admin';
        }
        $stmt_admin->close();

        // Buscar en alumno si no se encontró en admin
        if (!$user) {
            $stmt_alumno = $conexion->prepare("SELECT ID_Cuil_Alumno, Nombre_Alumno, Apellido_Alumno, Email_Alumno, Password FROM alumno WHERE ID_Cuil_Alumno = ?");
            $stmt_alumno->bind_param("s", $identificador);
            $stmt_alumno->execute();
            $result = $stmt_alumno->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $email = $user['Email_Alumno'];
                $nombre = $user['Nombre_Alumno'] . ' ' . $user['Apellido_Alumno'];
                $user_type = 'alumno';
            }
            $stmt_alumno->close();
        }

        if ($user && !empty($email)) {
            // Generar token
            $token = bin2hex(random_bytes(32));
            $token_hash = hash("sha256", $token);
            $expiry = date("Y-m-d H:i:s", time() + 60 * 30); // 30 minutos de expiración

            $table = ($user_type === 'admin') ? 'admin' : 'alumno';
            $id_column = ($user_type === 'admin') ? 'Legajo' : 'ID_Cuil_Alumno';

            $sql = "UPDATE $table SET reset_token_hash = ?, reset_token_expires_at = ? WHERE $id_column = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sss", $token_hash, $expiry, $identificador);
            $stmt->execute();

            if ($stmt->affected_rows) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sollione2004@gmail.com'; // tu correo
                    $mail->Password   = 'masu hqty zqfc pudz'; // tu contraseña de aplicación
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('sollione2004@gmail.com', 'Sistema de Validacion UTN FRH');
                    $mail->addAddress($email, $nombre);

                    $mail->isHTML(true);
                    $mail->Subject = 'Restablecimiento de Contrasena';
                    $reset_link = "http://{$_SERVER['HTTP_HOST']}/Sistema-De-Validacion-UTN-FRH/PHP/reset_contrasena.php?token=$token";
                    $mail->Body    = "Hola $nombre,<br><br>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el siguiente enlace para continuar:<br><a href='$reset_link'>Restablecer Contraseña</a><br><br>Si no solicitaste esto, puedes ignorar este correo.<br><br>Saludos,<br>Equipo de UTN FRH.";

                    $mail->send();
                    $success = 'Se ha enviado un enlace de recuperación a su correo electrónico.';
                } catch (Exception $e) {
                    $error = "No se pudo enviar el correo. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Error al procesar la solicitud. Intente de nuevo.";
            }
        } else {
            $error = "No se encontró una cuenta activa con el CUIL/Legajo proporcionado, o no tiene un correo electrónico asociado.";
        }
        $conexion->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - UTN FRH</title>
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/iniciosesion.css">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../index.html"><img src="../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
        </div>
    </header>
    <main class="login-page">
        <div class="login-container">
            <h1 class="login-title">Recuperar Contraseña</h1>
            <p style="text-align: center; margin-bottom: 1.5rem;">Ingrese su CUIL o Legajo para recibir un enlace de recuperación en su correo.</p>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-message" style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 1rem; margin-bottom: 1rem; border-radius: 5px;"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <form class="login-form" action="olvido_contrasena.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="identificador">Legajo (Admin) o CUIL (Alumno)</label>
                    <input type="text" id="identificador" name="identificador" placeholder="Ingrese su Legajo o CUIL" required>
                </div>
                <button type="submit" class="submit-btn">ENVIAR ENLACE</button>
                <div class="form-options" style="text-align: center; margin-top: 1rem;">
                    <a href="iniciosesion.php">Volver a Iniciar Sesión</a>
                </div>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-info" style="text-align: center; width: 100%;">
                <p>París 532, Haedo (1706) | Buenos Aires, Argentina</p>
                <p>extension@frh.utn.edu.ar</p>
            </div>
        </div>
    </footer>
</body>
</html>