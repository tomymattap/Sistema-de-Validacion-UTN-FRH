<?php session_start(); ?>
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
    <link rel="stylesheet" href="../../CSS/inscripciones.css">
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
                    <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <button class="user-menu-toggle">Hola, <?php session_start(); echo htmlspecialchars($_SESSION['user_name']); ?>. <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <ul>
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <li><a href="inscripciones.php">Inscripciones</a></li>
                        <li><a href="certificaciones.php">Certificaciones</a></li>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

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
                    <li><a href="../../index.html">Inicio</a></li>
                    <li><a href="../../HTML/sobrenosotros.html">Sobre Nosotros</a></li>
                    <li><a href="../../HTML/contacto.html">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-dynamic-nav" id="footer-dynamic-nav">
                <h4>Acceso</h4>
                <ul>
                    <li><a href="perfil.php">Perfil</a></li>
                    <li><a href="certificaciones.php">Certificaciones</a></li>
                    <li><a href="#">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="../../JavaScript/inscripciones.js"></script>
</body>
</html>