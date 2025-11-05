<?php
session_start();
// Redirige si el usuario ya ha iniciado sesión
if (isset($_SESSION['user_rol'])) {
    if ($_SESSION['user_rol'] == 1) { // Admin
        header('Location: ADMIN/gestionarinscriptos.php');
    } else { // Alumno
        header('Location: ALUMNO/perfil.php');
    }
    exit;
}

require_once 'conexion.php'; 
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login_input = $_POST['login-input'] ?? '';
    $password = $_POST['password'] ?? '';

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
        if ($user && ($password == $user['Password'])) { // Se mantiene la comparación directa según el código existente
            if ($is_admin) {
                $_SESSION['user_id'] = $user['ID_Admin'];
                $_SESSION['user_name'] = $user['Nombre'];
                $_SESSION['user_rol'] = 1; // Rol Admin
                header('Location: ADMIN/verinscriptos.php');
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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - UTN FRH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/iniciosesion.css">
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../index.html"><img src="../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.html">VALIDAR</a></li>
                    <li><a href="../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <a href="iniciosesion.php" class="btn-sesion">INICIAR SESIÓN</a>
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Menú Off-canvas -->
    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav>
            <ul>
                <li><a href="../index.html">VALIDAR</a></li>
                <li><a href="../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../HTML/contacto.html">CONTACTO</a></li>
                <li><a href="iniciosesion.php">INICIAR SESIÓN</a></li>
            </ul>
        </nav>
    </div>

    <main class="login-page">
        <div class="login-container">
            <div class="login-logo">
                <img src="../Imagenes/UTNLogo_InicioSesion.png" alt="Logo UTN">
            </div>
            <h1 class="login-title">Iniciar Sesión</h1>
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form class="login-form" action="iniciosesion.php" method="POST">
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

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-logo-info">
                <img src="../Imagenes/UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
                <div class="footer-info">
                    <p>París 532, Haedo (1706)</p>
                    <p>Buenos Aires, Argentina</p>
                    <br>
                    <p>Número de teléfono del depto.</p>
                    <br>
                    <p>extension@frh.utn.edu.ar</p>
                </div>
            </div>
            <div class="footer-social-legal">
                <div class="footer-social">
                    <a href="https://www.youtube.com/@facultadregionalhaedo-utn3647" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.linkedin.com/school/utn-facultad-regional-haedo/" target="_blank"><i class="fab fa-linkedin"></i></a>
                </div>
                <div class="footer-legal">
                    <a href="mailto:extension@frh.utn.edu.ar">Contacto</a>
                    <br> 
                    <a href="#politicas">Políticas de Privacidad</a>
                </div>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-nav">
                <h4>Navegación</h4>
                <ul>
                    <li><a href="../index.html">Validar</a></li>
                    <li><a href="../HTML/sobrenosotros.html">Sobre Nosotros</a></li>
                    <li><a href="../HTML/contacto.html">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-dynamic-nav" id="footer-dynamic-nav">
                <h4>Acceso</h4>
                <ul>
                    <li><a href="iniciosesion.php">Iniciar Sesión</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="../JavaScript/general.js"></script>
    <script src="../JavaScript/iniciosesion.js"></script>
</body>
</html>
