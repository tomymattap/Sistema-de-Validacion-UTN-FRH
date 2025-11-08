<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

require_once 'conexion.php';
$token = $_GET['token'] ?? '';
$error = null;
$show_form = false;

// Definir rutas localmente
$base_path = '../'; // Desde PHP/ a la raíz del proyecto
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';

if (empty($token)) {
    die("Token no proporcionado.");
}

$token_hash = hash("sha256", $token);

// Buscar el token en ambas tablas
$sql_admin = "SELECT * FROM admin WHERE reset_token_hash = ?";
$stmt_admin = $conexion->prepare($sql_admin);
$stmt_admin->bind_param("s", $token_hash);
$stmt_admin->execute();
$result_admin = $stmt_admin->get_result();

$user = null;
$user_type = '';

if ($result_admin->num_rows > 0) {
    $user = $result_admin->fetch_assoc();
    $user_type = 'admin';
} else {
    $stmt_admin->close();
    $sql_alumno = "SELECT * FROM alumno WHERE reset_token_hash = ?";
    $stmt_alumno = $conexion->prepare($sql_alumno);
    $stmt_alumno->bind_param("s", $token_hash);
    $stmt_alumno->execute();
    $result_alumno = $stmt_alumno->get_result();
    if ($result_alumno->num_rows > 0) {
        $user = $result_alumno->fetch_assoc();
        $user_type = 'alumno';
    }
    $stmt_alumno->close();
}

if (!$user) {
    die("Token inválido.");
}

if (strtotime($user['reset_token_expires_at']) <= time()) {
    die("El token ha expirado.");
}

$show_form = true;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificación del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Error de validación. Por favor, intente de nuevo.";
    }

    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!$error && (empty($password) || empty($password_confirm))) {
        $error = "Ambos campos de contraseña son obligatorios.";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Hashear la nueva contraseña para un almacenamiento seguro.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $table = ($user_type === 'admin') ? 'admin' : 'alumno';
        $id_column = ($user_type === 'admin') ? 'Legajo' : 'ID_Cuil_Alumno';
        $id_value = ($user_type === 'admin') ? $user['Legajo'] : $user['ID_Cuil_Alumno'];

        $sql = "UPDATE $table SET Password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL, first_login_done = 1 WHERE $id_column = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("ss", $hashed_password, $id_value);

        if ($stmt->execute()) {
            header('Location: iniciosesion.php?reset=exitoso');
            exit;
        } else {
            $error = "Hubo un error al actualizar la contraseña.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - UTN FRH</title>
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/iniciosesion.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
            <h1 class="login-title">Restablecer Contraseña</h1>
            <?php if ($show_form): ?>
                <?php if ($error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form class="login-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" placeholder="Ingrese su nueva contraseña" required autocomplete="new-password">
                            <i class="fas fa-eye-slash" data-toggle-for="password"></i>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmar Nueva Contraseña</label>
                        <div class="password-wrapper">
                            <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirme su contraseña" required autocomplete="new-password">
                            <i class="fas fa-eye-slash" data-toggle-for="password_confirm"></i>
                        </div>
                    </div>
                    <button type="submit" class="submit-btn">CAMBIAR CONTRASEÑA</button>
                </form>
            <?php endif; ?>
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
    <script>
        // Script para mostrar/ocultar contraseña
        document.querySelectorAll('.password-wrapper i[data-toggle-for]').forEach(icon => {
            icon.addEventListener('click', function () {
                const inputId = this.getAttribute('data-toggle-for');
                const input = document.getElementById(inputId);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
                input.type = input.type === 'password' ? 'text' : 'password';
            });
        });
    </script>
</body>
</html>