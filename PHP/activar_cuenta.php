<?php
session_start();

// Si el usuario no pasó por la página de registro, no puede estar aquí.
if (!isset($_SESSION['activacion_identificador']) || !isset($_SESSION['activacion_tipo'])) {
    header('Location: registro.php');
    exit;
}

require_once 'conexion.php';
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $identificador = $_SESSION['activacion_identificador'];
    $tipo = $_SESSION['activacion_tipo'];

    if (empty($password) || empty($password_confirm)) {
        $error = "Ambos campos de contraseña son obligatorios.";
    } elseif ($password !== $password_confirm) {
        $error = "Las contraseñas no coinciden.";
    } else {
        // Aquí deberías hashear la contraseña. Por ahora, la guardamos en texto plano como en tu código original.
        // $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $hashed_password = $password; // Manteniendo la lógica original

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
        <!-- ... contenido del footer ... -->
    </footer>

    <script>
        // Script para mostrar/ocultar contraseña
        document.getElementById('toggle-password').addEventListener('click', function () {
            const password = document.getElementById('password');
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
            password.type = password.type === 'password' ? 'text' : 'password';
        });
        document.getElementById('toggle-password-confirm').addEventListener('click', function () {
            const password = document.getElementById('password_confirm');
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
            password.type = password.type === 'password' ? 'text' : 'password';
        });
    </script>
</body>
</html>