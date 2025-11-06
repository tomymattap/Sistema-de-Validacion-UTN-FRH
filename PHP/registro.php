<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

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
        $user_type = '';

        // Intentar como administrador
        $stmt_admin = $conexion->prepare("SELECT Legajo, Password FROM admin WHERE Legajo = ?");
        $stmt_admin->bind_param("s", $identificador);
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();

        if ($result_admin->num_rows > 0) {
            $user = $result_admin->fetch_assoc();
            $user_type = 'admin';
        }
        $stmt_admin->close();

        // Si no es admin, intentar como alumno
        if (!$user) {
            $stmt_alumno = $conexion->prepare("SELECT ID_Cuil_Alumno, Password FROM alumno WHERE ID_Cuil_Alumno = ?");
            $stmt_alumno->bind_param("s", $identificador);
            $stmt_alumno->execute();
            $result_alumno = $stmt_alumno->get_result();
            if ($result_alumno->num_rows > 0) {
                $user = $result_alumno->fetch_assoc();
                $user_type = 'alumno';
            }
            $stmt_alumno->close();
        }

        if ($user) {
            // Si el usuario existe pero ya tiene contraseña, no puede registrarse de nuevo.
            if (!empty($user['Password'])) {
                $error = "Esta cuenta ya ha sido registrada. Si olvidó su contraseña, por favor utilice la opción de recuperación.";
            } else {
                // El usuario existe y no tiene contraseña, redirigir a la activación.
                $_SESSION['activacion_identificador'] = $identificador;
                $_SESSION['activacion_tipo'] = $user_type;
                header('Location: activar_cuenta.php');
                exit;
            }
        } else {
            $error = "El CUIL o Legajo ingresado no se encuentra en nuestros registros.";
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
    <title>Registro de Usuario - UTN FRH</title>
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
            <div class="login-logo">
                <img src="<?php echo htmlspecialchars($img_path); ?>UTNLogo_InicioSesion.png" alt="Logo UTN">
            </div>
            <h1 class="login-title">Activar Cuenta</h1>
            <p style="text-align: center; margin-bottom: 1.5rem;">Ingrese su CUIL o Legajo para crear su contraseña y activar su cuenta.</p>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="login-form" action="registro.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="identificador">Legajo (Admin) o CUIL (Alumno)</label>
                    <input type="text" id="identificador" name="identificador" placeholder="Ingrese su Legajo o CUIL" required>
                </div>
                <button type="submit" class="submit-btn">CONTINUAR</button>
                <div class="form-options" style="text-align: center; margin-top: 1rem;">
                    <a href="iniciosesion.php">¿Ya tienes una cuenta? Inicia Sesión</a>
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