<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Si el usuario no pasó por la página de registro, no puede estar aquí.
if (!isset($_SESSION['activacion_identificador']) || !isset($_SESSION['activacion_tipo'])) {
    header('Location: registro.php');
    exit;
}

// Definir rutas localmente
$base_path = '../'; // Desde PHP/ a la raíz del proyecto
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';

require_once 'conexion.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificación del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Error de validación. Por favor, intente de nuevo.";
    }

    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $identificador = $_SESSION['activacion_identificador'];
    $tipo = $_SESSION['activacion_tipo'];

    if (!$error && (empty($password) || empty($password_confirm))) {
        $error = "Ambos campos de contraseña son obligatorios.";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Hashear la contraseña para un almacenamiento seguro.
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        if ($tipo === 'admin') {
            $stmt = $conexion->prepare("UPDATE admin SET Password = ? WHERE Legajo = ?"); // Admin no tiene first_login_done
        } else {
            $stmt = $conexion->prepare("UPDATE alumno SET Password = ?, first_login_done = 1 WHERE ID_Cuil_Alumno = ?");
        }

        $stmt->bind_param("ss", $hashed_password, $identificador);

        if ($stmt->execute()) {
            // Limpiar la sesión y redirigir al inicio de sesión con un mensaje de éxito.
            unset($_SESSION['activacion_identificador']);
            unset($_SESSION['activacion_tipo']);
            session_destroy();
            header('Location: iniciosesion.php?registro=exitoso');
            exit;
        } else {
            $error = "Hubo un error al activar su cuenta. Por favor, intente de nuevo.";
        }
        $stmt->close();
        $conexion->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Contraseña - UTN FRH</title>
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
            <h1 class="login-title">Crear Contraseña</h1>
            <p style="text-align: center; margin-bottom: 1.5rem;">¡Último paso! Crea una contraseña para tu cuenta.</p>

            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form class="login-form" action="activar_cuenta.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="password">Nueva Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Ingrese su nueva contraseña" required>
                        <i class="fas fa-eye-slash" id="toggle-password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirme su contraseña" required>
                        <i class="fas fa-eye-slash" id="toggle-password-confirm"></i>
                    </div>
                </div>
                <button type="submit" class="submit-btn">FINALIZAR REGISTRO</button>
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