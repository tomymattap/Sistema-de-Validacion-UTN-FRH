<?php
// Incluir header.php para la lógica de sesión y CSRF
$page_title = 'Iniciar Sesión - UTN FRH';
$extra_styles = ['iniciosesion.css'];
include('header.php');

// Redirige si el usuario ya ha iniciado sesión
if (isset($_SESSION['user_rol'])) {
    $redirect_url = ($_SESSION['user_rol'] == 1) ? 'ADMIN/gestionarinscriptos.php' : 'ALUMNO/perfil.php';
    // Usamos un script de JS para la redirección porque el header ya se envió.
    echo '<script>window.location.href = "' . htmlspecialchars($redirect_url) . '";</script>';
    exit;
}

require_once 'conexion.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = $_POST['login-input'] ?? '';
    $password = $_POST['password'] ?? '';

    // Verificación del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = "Error de validación. Por favor, intente de nuevo.";
        // Opcional: invalidar la sesión y forzar un nuevo inicio de sesión
    }

    if (empty($login_input) || empty($password)) {
        $error = "El legajo/CUIL y la contraseña son obligatorios.";
    } else {
        $user = null;
        $is_admin = false;

        // Intentar como administrador
        $stmt_admin = $conexion->prepare("SELECT ID_Admin, Nombre, Legajo, Password, 1 as Rol FROM admin WHERE Legajo = ?");
        $stmt_admin->bind_param("s", $login_input);
        $stmt_admin->execute();
        $result = $stmt_admin->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $is_admin = true;
        }
        $stmt_admin->close();

        // Si no es admin, intentar como alumno
        if (!$user) {
            $stmt_alumno = $conexion->prepare("SELECT ID_Cuil_Alumno, Nombre_Alumno, Apellido_Alumno, Password, 2 as Rol FROM alumno WHERE ID_Cuil_Alumno = ?");
            $stmt_alumno->bind_param("s", $login_input);
            $stmt_alumno->execute();
            $result = $stmt_alumno->get_result();
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
            }
            $stmt_alumno->close();
        }

        // Verificar contraseña y establecer sesión
        // Se utiliza password_verify() para comparar la contraseña ingresada con el hash almacenado.
        if ($user && password_verify($password, $user['Password'])) {
            if ($is_admin) {
                $_SESSION['user_id'] = $user['ID_Admin'];
                $_SESSION['user_name'] = $user['Nombre'];
                $_SESSION['user_rol'] = 1; // Rol Admin
                header('Location: ADMIN/gestionarinscriptos.php');
            } else {
                $_SESSION['user_id'] = $user['ID_Cuil_Alumno'];
                $_SESSION['user_name'] = $user['Nombre_Alumno'];
                $_SESSION['user_rol'] = 2; // Rol Alumno
                header('Location: ALUMNO/perfil.php');
            }
            exit;
        } else {
            $error = "Usuario o contraseña inválidos.";
        }
    }
    $conexion->close();
}
?>
    <main class="login-page">
        <div class="login-container">
            <div class="login-logo">
                <img src="<?php echo htmlspecialchars($img_path); ?>UTNLogo_InicioSesion.png" alt="Logo UTN">
            </div>
            <h1 class="login-title">Iniciar Sesión</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="login-form" action="iniciosesion.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                <div class="form-group">
                    <label for="login-input">Legajo (Admin) o CUIL (Alumno)</label>
                    <input type="text" id="login-input" name="login-input" placeholder="Ingrese su Legajo o CUIL" required>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
                        <i class="fas fa-eye-slash" id="toggle-password"></i>
                    </div>
                </div>
                <div class="form-options">
                    <a href="olvido_contrasena.php" class="forgot-password">¿Olvidó su contraseña?</a>
                </div>
                <button type="submit" class="submit-btn">ACCEDER</button>
                <div class="form-options" style="text-align: center; margin-top: 1rem;">
                    <p>¿No tienes una cuenta? <a href="registro.php">Crear cuenta</a></p>
                </div>
            </form>
        </div>
    </main>

<?php
$extra_scripts = ['iniciosesion.js'];
include('footer.php');
?>
