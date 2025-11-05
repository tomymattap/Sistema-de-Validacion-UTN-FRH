<?php
$page_title = 'Restablecer Contraseña - UTN FRH';
$extra_styles = ['iniciosesion.css'];
include('header.php');

require_once 'conexion.php'; // La conexión a la BD
$token = $_GET['token'] ?? '';
$error = null;
$show_form = false;

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

        $sql = "UPDATE $table SET Password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE $id_column = ?";
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
                        <input type="password" id="password" name="password" placeholder="Ingrese su nueva contraseña" required>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirmar Nueva Contraseña</label>
                        <input type="password" id="password_confirm" name="password_confirm" placeholder="Confirme su contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">CAMBIAR CONTRASEÑA</button>
                </form>
            <?php endif; ?>
        </div>
    </main>
<?php
include('footer.php');
?>