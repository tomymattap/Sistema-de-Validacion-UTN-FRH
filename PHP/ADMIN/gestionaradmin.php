<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
    exit;
}

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
$current_page = 'gestionaradmin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Administradores - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>gestionaradmin.css">
</head>
<body>

<!-- ======================= HEADER ========================= -->
<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a>
        </div>
        <nav class="main-nav hide-on-mobile">
            <ul>
                <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
                <li><a href="<?php echo $html_path; ?>sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
        <div class="session-controls hide-on-mobile">
            <?php if (isset($_SESSION['user_name'])): ?>
                <div class="user-menu-container">
                    <a href="#" class="btn-sesion user-menu-toggle">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-menu">
                        <ul>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php" class="active">Gestionar Administradores</a></li>
                            <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $php_path; ?>iniciosesion.php" class="btn-sesion">INICIAR SESIÓN</a>
            <?php endif; ?>
        </div>
        <button class="hamburger-menu" aria-label="Abrir menú">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
</header>

<div class="off-canvas-menu" id="off-canvas-menu">
    <button class="close-btn" aria-label="Cerrar menú">&times;</button>
    <nav>
        <ul>
            <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
            <li><a href="<?php echo $html_path; ?>sobrenosotros.html">SOBRE NOSOTROS</a></li>
            <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
            <li id="mobile-session-section">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="submenu">
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php" class="active">Gestionar Administradores</a></li>
                        <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                    </ul>
                <?php else: ?>
                    <a href="<?php echo $php_path; ?>iniciosesion.php">INICIAR SESIÓN</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</div>

<!-- ===================== MAIN CONTENT ======================= -->
<main class="admin-section">
    <div class="admin-container">
        <h1 class="main-title">Gestionar Administradores</h1>

        <div class="actions-container">
            <button class="btn-agregar"><i class="fas fa-plus"></i> Agregar Administrador</button>
            <div class="search-container">
                <input type="text" id="liveSearch" placeholder="Buscar por nombre o apellido...">
            </div>
        </div>

        <div id="error-container" class="error-container" style="display: none;"></div>

        <div class="table-container">
            <table class="tabla-admins">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Legajo</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Los datos se cargarán aquí por JavaScript -->
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- ======================= FOOTER ========================= -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-logo-info">
            <img src="<?php echo $img_path; ?>UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
            <div class="footer-info">
                <p>París 532, Haedo (1706)</p>
                <p>Buenos Aires, Argentina</p><br>
                <p>Número de teléfono del depto.</p><br>
                <p>extension@frh.utn.edu.ar</p>
            </div>
        </div>
        <div class="footer-social-legal">
            <div class="footer-social">
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
            <div class="footer-legal">
                <a href="#">Contacto</a><br>
                <a href="#">Políticas de Privacidad</a>
            </div>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-nav">
            <h4>Navegación</h4>
            <ul>
                <li><a href="<?php echo $base_path; ?>index.html">Validar</a></li>
                <li><a href="<?php echo $html_path; ?>sobrenosotros.html">Sobre Nosotros</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-dynamic-nav">
            <?php if (isset($_SESSION['user_name'])):
                $user_rol = $_SESSION['user_rol'] == 1 ? 'Admin' : 'Alumno';
                $is_admin = $_SESSION['user_rol'] == 1;
            ?>
                <h4><?php echo $user_rol; ?></h4>
                <ul>
                    <?php if ($is_admin): ?>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <li><a href="#">Mi Perfil</a></li>
                        <li><a href="#">Inscripciones</a></li>
                        <li><a href="#">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="<?php echo $php_path; ?>iniciosesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>
<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

<script src="<?php echo $js_path; ?>general.js"></script>
<script src="<?php echo $js_path; ?>gestionaradmin.js"></script>

</body>
</html>
