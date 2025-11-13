<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// **BLOQUE DE SEGURIDAD**
// La única condición para estar aquí es que se haya iniciado sesión por primera vez
// y se deba forzar el cambio de contraseña.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['force_password_change'])) {
    header('Location: ../inicio_sesion.php');
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Definir rutas localmente
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';

require_once '../conexion.php';
$error = null;
$success = null;

$user_id = $_SESSION['user_id']; // Aquí user_id es el ID_Admin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Error de validación. Por favor, intente de nuevo.";
    }

    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $new_password_confirm = $_POST['new_password_confirm'] ?? '';

    if (!$error && (empty($current_password) || empty($new_password) || empty($new_password_confirm))) {
        $error = "Todos los campos de contraseña son obligatorios.";
    } elseif ($new_password !== $new_password_confirm) {
        $error = "La nueva contraseña y su confirmación no coinciden.";
    } else {
        $stmt = $conexion->prepare("SELECT Password FROM admin WHERE ID_Admin = ?");
        $stmt->bind_param("s", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($current_password, $user['Password'])) {
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt_update = $conexion->prepare("UPDATE admin SET Password = ?, first_login_done = 1 WHERE ID_Admin = ?");
            $stmt_update->bind_param("ss", $hashed_new_password, $user_id);

            if ($stmt_update->execute()) {
                // --- COMPLETAR EL INICIO DE SESIÓN ---
                $stmt_get_data = $conexion->prepare("SELECT Nombre FROM admin WHERE ID_Admin = ?");
                $stmt_get_data->bind_param("s", $user_id);
                $stmt_get_data->execute();
                $user_data = $stmt_get_data->get_result()->fetch_assoc();
                
                $_SESSION['user_name'] = $user_data['Nombre'];
                $_SESSION['user_rol'] = 1; // Establecer el rol de admin
                unset($_SESSION['force_password_change']);
                
                $success = "Su contraseña ha sido actualizada correctamente. Será redirigido al panel de administración.";
                header('Refresh: 2; URL=gestionar_inscriptos.php');
            } else {
                $error = "Hubo un error al actualizar su contraseña.";
            }
            $stmt_update->close();
        } else {
            $error = "La contraseña actual (su legajo) es incorrecta.";
        }
    }
    $conexion->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña Obligatorio - Admin</title>
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>INICIO/inicio_sesion.css"> <!-- Reutilizamos estilos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo"><a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a></div>
            <div class="session-controls" style="display: block;"><a href="../logout.php" class="btn-sesion">Cerrar Sesión</a></div>
        </div>
    </header>
    <main class="login-page">
        <div class="login-container">
            <h1 class="login-title">Cambiar Contraseña de Administrador</h1>
            <p style="text-align: center; margin-bottom: 1.5rem;">Por seguridad, debe cambiar su contraseña inicial.</p>

            <?php if ($error) echo "<div class='error-message'>" . htmlspecialchars($error) . "</div>"; ?>
            <?php if ($success) echo "<div class='success-message'>" . htmlspecialchars($success) . "</div>"; ?>

            <?php if (!$success): ?>
            <form class="login-form" action="cambiar_contrasena_obligatorio.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="current_password">Contraseña Actual (su Legajo)</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" placeholder="Ingrese su Legajo" required autocomplete="current-password">
                        <i class="fas fa-eye-slash" data-toggle-for="current_password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" placeholder="Ingrese su nueva contraseña" required autocomplete="new-password">
                        <i class="fas fa-eye-slash" data-toggle-for="new_password"></i>
                    </div>
                </div>
                <div class="form-group">
                    <label for="new_password_confirm">Confirmar Nueva Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password_confirm" name="new_password_confirm" placeholder="Confirme su nueva contraseña" required autocomplete="new-password">
                        <i class="fas fa-eye-slash" data-toggle-for="new_password_confirm"></i>
                    </div>
                </div>
                <button type="submit" class="submit-btn">CAMBIAR CONTRASEÑA</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
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