<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// **BLOQUE DE SEGURIDAD REFORZADO**
// La única condición para estar aquí es que se haya iniciado sesión por primera vez
// y se deba forzar el cambio de contraseña. No se debe verificar el rol aún.
if (!isset($_SESSION['user_id']) || !isset($_SESSION['force_password_change'])) {
    header('Location: ../inicio_sesion.php'); // Si no se cumplen, se va a iniciar sesión.
    exit;
}

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Definir rutas localmente
$base_path = '../../'; // Desde ALUMNO/ a la raíz del proyecto
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';

require_once '../conexion.php';
$error = null;
$success = null;

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificación del token CSRF
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
        // Obtener la contraseña actual del alumno de la base de datos
        $stmt = $conexion->prepare("SELECT Password FROM alumno WHERE ID_Cuil_Alumno = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($current_password, $user['Password'])) {
            // La contraseña actual es correcta, proceder a actualizar
            $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt_update = $conexion->prepare("UPDATE alumno SET Password = ?, first_login_done = 1 WHERE ID_Cuil_Alumno = ?");
            $stmt_update->bind_param("si", $hashed_new_password, $user_id);

            if ($stmt_update->execute()) {
                // --- COMPLETAR EL INICIO DE SESIÓN ---
                // Ahora que la contraseña se cambió, creamos la sesión de usuario completa.
                $stmt_get_name = $conexion->prepare("SELECT Nombre_Alumno FROM alumno WHERE ID_Cuil_Alumno = ?");
                $stmt_get_name->bind_param("i", $user_id);
                $stmt_get_name->execute();
                $user_data = $stmt_get_name->get_result()->fetch_assoc();
                $_SESSION['user_name'] = $user_data['Nombre_Alumno'];
                $_SESSION['user_rol'] = 2; // Se establece el rol de alumno.
                unset($_SESSION['force_password_change']); // Eliminar la bandera de cambio forzado
                
                $success = "Su contraseña ha sido actualizada correctamente. Será redirigido a su perfil.";
                // Redirigir después de un breve retraso para mostrar el mensaje de éxito
                header('Refresh: 3; URL=perfil.php');
            } else {
                $error = "Hubo un error al actualizar su contraseña. Por favor, intente de nuevo.";
            }
            $stmt_update->close();
        } else {
            $error = "La contraseña actual ingresada es incorrecta.";
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
    <title>Cambio de Contraseña Obligatorio - UTN FRH</title>
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>INICIO/inicio_sesion.css"> <!-- Reutilizamos estilos de inicio de sesión -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body class="login-page">
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <div class="session-controls" style="display: block;">
                <a href="../logout.php" class="btn-sesion">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    <main class="login-page">
        <div class="login-container">
            <h1 class="login-title">Cambiar Contraseña</h1>
            <p style="text-align: center; margin-bottom: 1.5rem;">Por seguridad, debe cambiar su contraseña inicial.</p>

            <?php if ($error) echo "<div class='error-message'>" . htmlspecialchars($error) . "</div>"; ?>
            <?php if ($success) echo "<div class='success-message'>" . htmlspecialchars($success) . "</div>"; ?>

            <?php if (!$success): ?>
            <form class="login-form" action="cambiar_contrasena_obligatorio.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="current_password">Contraseña Actual (su CUIL)</label>
                    <div class="password-wrapper">
                        <input type="password" id="current_password" name="current_password" placeholder="Ingrese su CUIL" required autocomplete="current-password">
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