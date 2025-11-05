<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener el nombre del script actual para marcar el enlace activo
$current_page = basename($_SERVER['PHP_SELF']);

// Definir las rutas base según la ubicación del script
$base_path = (strpos($current_page, 'verinscriptos.php') !== false || strpos($current_page, 'perfil.php') !== false || strpos($current_page, 'inscripciones.php') !== false || strpos($current_page, 'certificaciones.php') !== false) ? '../../' : '../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Título dinámico si se define en la página que lo incluye -->
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'UTN FRH'; ?></title>

    <!-- Fuentes y estilos -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- Hojas de estilo -->
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <!-- Cargar hojas de estilo adicionales si se especifican -->
    <?php if (isset($extra_styles) && is_array($extra_styles)): ?>
        <?php foreach ($extra_styles as $style): ?>
            <link rel="stylesheet" href="<?php echo $css_path . $style; ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>

            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo $base_path; ?>index.html" class="<?php echo ($current_page == 'index.html') ? 'active' : ''; ?>">VALIDAR</a></li>
                    <li><a href="<?php echo $html_path; ?>sobrenosotros.html" class="<?php echo ($current_page == 'sobrenosotros.html') ? 'active' : ''; ?>">SOBRE NOSOTROS</a></li>
                    <li><a href="<?php echo $html_path; ?>contacto.html" class="<?php echo ($current_page == 'contacto.html') ? 'active' : ''; ?>">CONTACTO</a></li>
                </ul>
            </nav>

            <div class="session-controls hide-on-mobile">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <div class="user-menu-container">
                        <a href="#" class="btn-sesion user-menu-toggle">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-menu">
                            <ul>
                                <?php if ($_SESSION['user_rol'] == 1): // Admin ?>
                                    <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php" class="<?php echo ($current_page == 'gestionarinscriptos.php') ? 'active' : ''; ?>">Gestionar Inscriptos</a></li>
                                    <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php" class="<?php echo ($current_page == 'gestionaradmin.php') ? 'active' : ''; ?>">Gestionar Administradores</a></li>
                                    <li><a href="<?php echo $html_path; ?>gestionarcursos.html" class="<?php echo ($current_page == 'gestionarcursos.html') ? 'active' : ''; ?>">Gestionar Cursos</a></li>
                                    <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php" class="<?php echo ($current_page == 'seleccionar_alum_certif.php') ? 'active' : ''; ?>">Emitir Certificados</a></li>
                                <?php else: // Alumno ?>
                                    <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                                    <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li>
                                    <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                                <?php endif; ?>
                                <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $php_path; ?>iniciosesion.php" class="btn-sesion">INICIAR SESIÓN</a>
                <?php endif; ?>
            </div>

            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>

    <!-- Menú Off-canvas -->
    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav>
            <ul>
                <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
                <li><a href="<?php echo $html_path; ?>sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
                
                <li class="has-submenu show-on-mobile">
                    <?php if (isset($_SESSION['user_name'])): ?>
                        <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu">
                            <?php if ($_SESSION['user_rol'] == 1): // Admin ?>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php" class="<?php echo ($current_page == 'gestionarinscriptos.php') ? 'active' : ''; ?>">Gestionar Inscriptos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php" class="<?php echo ($current_page == 'gestionaradmin.php') ? 'active' : ''; ?>">Gestionar Administradores</a></li>
                                <li><a href="<?php echo $html_path; ?>gestionarcursos.html" class="<?php echo ($current_page == 'gestionarcursos.html') ? 'active' : ''; ?>">Gestionar Cursos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php" class="<?php echo ($current_page == 'seleccionar_alum_certif.php') ? 'active' : ''; ?>">Emitir Certificados</a></li>
                            <?php else: // Alumno ?>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                            <?php endif; ?>
                              <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                        </ul>
                    <?php else: ?>
                        <a href="<?php echo $php_path; ?>iniciosesion.php">INICIAR SESIÓN</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>
