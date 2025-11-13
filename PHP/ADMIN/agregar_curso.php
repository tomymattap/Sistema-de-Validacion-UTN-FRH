<?php
session_start();
include("../conexion.php");

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
$current_page = 'agregar_curso.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Curso - Admin</title>
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_cursos.css">
    
</head>
<body class="fade-in">
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav hide-on-mobile">
                <ul>
                    <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
                    <li><a href="<?php echo $html_path; ?>sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls hide-on-mobile">
                <?php if (isset($_SESSION['user_name'])): ?>
                    <div class="user-menu-container">
                        <a href="#" class="btn-sesion user-menu-toggle">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                        <div class="dropdown-menu">
                            <ul>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_admin.php" class="active">Gestionar Administradores</a></li>
                                <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?php echo $php_path; ?>inicio_sesion.php" class="btn-sesion">INICIAR SESIÓN</a>
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
                <li><a href="<?php echo $html_path; ?>sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
                <li id="mobile-session-section">
                    <?php if (isset($_SESSION['user_name'])):
                        $user_rol = $_SESSION['user_rol'];
                    ?>
                        <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                        <ul class="submenu">
                            <?php if ($user_rol == 1): // Admin ?>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                            <?php else: // Estudiante ?>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li> 
                                <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                        </ul>
                    <?php else: ?>
                        <a href="<?php echo $php_path; ?>inicio_sesion.php">INICIAR SESIÓN</a>
                    <?php endif; ?>
                </li>
            </ul>
        </nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            
            <div class="contenido-principal">

                <div id="header-container">
                    <h1 class="main-title">Agregar Curso</h1>
                    <a href="gestionar_cursos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
                </div>

                
                <div class="form-container">
                    <form action="insertar_curso.php" method="POST" class="form-grid">
                        <div class="form-group">
                            <label for="nombre_curso">Nombre del Curso</label>
                            <input type="text" id="nombre_curso" name="nombre_curso" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categoría</label>
                            <input type="text" id="categoria" name="categoria" required>
                        </div>
                        <div class="form-group">
                            <label for="modalidad">Modalidad (opcional)</label>
                            <input type="text" id="modalidad" name="modalidad">
                        </div>
                        <div class="form-group">
                            <label for="docente">Docente (opcional)</label>
                            <input type="text" id="docente" name="docente">
                        </div>
                        <div class="form-group">
                            <label for="carga_horaria">Carga Horaria (opcional)</label>
                            <input type="text" id="carga_horaria" name="carga_horaria" placeholder="Ej: 40 horas">
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="GENUINO">Genuino</option>
                                <option value="CERTIFICACION">Certificación</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción (opcional)</label>
                            <textarea id="descripcion" name="descripcion"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="requisitos">Requisitos (opcional)</label>
                            <textarea id="requisitos" name="requisitos"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-reset"><i class="fas fa-undo"></i> Limpiar</button>
                            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

<!-- ======================= FOOTER ========================= -->
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
                <li><a href="../../index.html">Validar</a></li>
                <li><a href="../../HTML/sobre_nosotros.html">Sobre Nosotros</a></li>
                <li><a href="../../HTML/contacto.html">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-dynamic-nav">
            <?php if (isset($_SESSION['user_name'])): ?>
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Estudiante'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <br>
                        <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="../ALUMNO/perfil.php">Mi Perfil</a></li>
                        <br>
                        <li><a href="../ALUMNO/inscripciones.php">Inscripciones</a></li>
                        <br>
                        <li><a href="../ALUMNO/certificaciones.php">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="../inicio_sesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>

<!-- ===================== FIN FOOTER ======================= -->

    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="../../JavaScript/general.js"></script>
    <script>
        fetch('../get_user_name.php')
            .then(response => response.json())
            .then(data => {
                const sessionControls = document.getElementById('session-controls');
                const mobileNav = document.querySelector('.off-canvas-menu nav ul');
                let sessionHTML = '';

                if (data.user_name) {
                    let dropdownMenu;
                    if (data.user_rol === 1) { // Admin
                        dropdownMenu = `
                            <button class="user-menu-toggle">Hola, ${data.user_name}. <i class="fas fa-chevron-down"></i></button>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                    <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                                    <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    } else {
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    window.location.href = '../inicio_sesion.php';
                }

                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>