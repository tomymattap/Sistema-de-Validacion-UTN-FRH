<?php
session_start();

// Verificar si el usuario está logueado y es un alumno
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    header("Location: ../inicio_sesion.php?error=acceso_denegado");
    exit();
}

// **BLOQUE DE SEGURIDAD: Forzar cambio de contraseña**
if (isset($_SESSION['force_password_change'])) {
    header('Location: cambiar_contrasena_obligatorio.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripciones - UTN FRH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ALUMNO/inscripciones.css">
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../../index.html">VALIDAR</a></li>
                    <!--<li> <a href="../../HTML/cursos.html">CURSOS</a> </li>-->
                    <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <!-- Contenido dinámico por JS -->
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

<div class="off-canvas-menu" id="off-canvas-menu">
    <button class="close-btn" aria-label="Cerrar menú">&times;</button>
    <nav>
        <ul>
            <li><a href="../../index.html">VALIDAR</a></li>
            <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
            <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            <li id="mobile-session-section">
                <?php if (isset($_SESSION['user_name'])):
                    $user_rol = $_SESSION['user_rol'];
                ?>
                    <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="submenu">
                        <?php if ($user_rol == 2): // Estudiante ?>
                            <li><a href="perfil.php">Mi Perfil</a></li>
                            <li><a href="inscripciones.php">Inscripciones</a></li> 
                            <li><a href="certificaciones.php">Certificaciones</a></li>
                        <?php endif; ?>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>
                    </ul>
                <?php else: ?>
                    <a href="../inicio_sesion.php">INICIAR SESIÓN</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</div>

    <main class="inscripciones-container">
        <div class="cursos-list">
            <h1>INSCRIPCIONES</h1>
            <h2>Mis cursos</h2>
            <div class="cursos-accordion">
                <div class="curso-item" data-course-id="curso1">
                    <div class="curso-header">
                        <h3>Instalación de Paneles Solares</h3>
                    </div>
                    <div class="curso-details">
                        <p><strong>Fecha de inicio:</strong> 01/08/2024</p>
                        <p><strong>Fecha de finalización:</strong> 30/11/2024</p>
                        <p><strong>Modalidad:</strong> Online</p>
                        <p><strong>Docente a Cargo:</strong> Juan Pérez</p>
                    </div>
                </div>
                <div class="curso-item" data-course-id="curso2">
                    <div class="curso-header">
                        <h3>Programming Essentials en Python</h3>
                    </div>
                    <div class="curso-details">
                        <p><strong>Fecha de inicio:</strong> 15/08/2024</p>
                        <p><strong>Fecha de finalización:</strong> 15/12/2024</p>
                        <p><strong>Modalidad:</strong> Presencial</p>
                        <p><strong>Docente a Cargo:</strong> María García</p>
                    </div>
                </div>
            </div>
        </div>
        <aside class="estado-aside">
            <h2>Estado</h2>
            <ul>
                <li id="estado-pendiente">Pendiente</li>
                <li id="estado-aceptado">Aceptado</li>
                <li id="estado-finalizado">Finalizado</li>
            </ul>
        </aside>
    </main>

    <footer class="site-footer">
    <div class="footer-container">
        <div class="footer-logo-info">
            <img src="../../Imagenes/UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
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
                <li><a href="<?php echo $html_path; ?>sobre_nosotros.html">Sobre Nosotros</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-dynamic-nav">
            <?php if (isset($_SESSION['user_name'])): ?>
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Estudiante'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="#">Mi Perfil</a></li>
                        <br>
                        <li><a href="#">Inscripciones</a></li>
                        <br>
                        <li><a href="#">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="<?php echo $php_path; ?>inicio_sesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    </footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../../JavaScript/general.js"></script>
    <script src="../../JavaScript/ALUMNO/inscripciones.js"></script>
    <script>
        fetch('../get_user_name.php')
            .then(response => response.json())
            .then(data => {
                const sessionControls = document.getElementById('session-controls');

                if (data.user_name) {
                    let dropdownMenu;
                    if (data.user_rol === 2) { // Estudiante
                        dropdownMenu = `
                            <button class="user-menu-toggle">Hola, ${data.user_name}. <i class="fas fa-chevron-down"></i></button>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="perfil.php">Mi Perfil</a></li>
                                    <li><a href="inscripciones.php">Inscripciones</a></li>
                                    <li><a href="certificaciones.php">Certificaciones</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                    } else if (data.user_rol === 1) { // Admin
                        // Redirigir si no es alumno
                        window.location.href = '../ADMIN/gestionar_inscriptos.php';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    // Redirigir si no está logueado
                    window.location.href = '../inicio_sesion.php?error=acceso_denegado';
                }
            });
    </script>
</body>
</html>