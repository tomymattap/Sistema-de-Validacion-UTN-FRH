<?php
$page_title = 'Crear Contraseña - UTN FRH';
$extra_styles = ['iniciosesion.css'];
include('header.php');

require_once 'conexion.php';

// Si el usuario no pasó por la página de registro, no puede estar aquí.
if (!isset($_SESSION['activacion_identificador']) || !isset($_SESSION['activacion_tipo'])) {
    header('Location: registro.php');
    exit;
}

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
            $stmt = $conexion->prepare("UPDATE admin SET Password = ? WHERE Legajo = ?");
        } else {
            $stmt = $conexion->prepare("UPDATE alumno SET Password = ? WHERE ID_Cuil_Alumno = ?");
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

<?php
$extra_scripts = ['iniciosesion.js']; // Reutilizamos el script para mostrar/ocultar contraseña
include('footer.php');
?>