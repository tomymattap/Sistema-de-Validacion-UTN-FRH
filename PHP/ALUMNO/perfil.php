<?php
session_start();
require '../conexion.php';

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

$user_id = $_SESSION['user_id'];

// Obtener datos del alumno
$sql = "SELECT Nombre_Alumno, Apellido_Alumno, DNI_Alumno, Email_Alumno, Telefono FROM alumno WHERE ID_Cuil_Alumno = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();

$stmt->close();
$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Estudiante - UTN FRH</title>
    <link rel="icon" href="../Imagenes/icon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ALUMNO/perfil.css">
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
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php" class="active">Gestionar Inscriptos</a></li>
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

    <main>
        <div class="profile-container">
            <h1>Perfil de Estudiante</h1>
            <form id="profile-form">
                <div class="profile-info">
                    <div class="profile-field">
                        <label for="nombre">Nombre</label>
                        <div class="value non-editable">
                            <span id="nombre"><?php echo htmlspecialchars($alumno['Nombre_Alumno']); ?></span>
                        </div>
                    </div>
                    <div class="profile-field">
                        <label for="apellido">Apellido</label>
                        <div class="value non-editable">
                            <span id="apellido"><?php echo htmlspecialchars($alumno['Apellido_Alumno']); ?></span>
                        </div>
                    </div>
                    <div class="profile-field">
                        <label for="dni">DNI</label>
                        <div class="value non-editable">
                            <span id="dni"><?php echo htmlspecialchars($alumno['DNI_Alumno']); ?></span>
                        </div>
                    </div>
                    <div class="profile-field">
                        <label for="email">Correo electrónico</label>
                        <div class="value" data-field="email">
                            <span class="display-value"><?php echo htmlspecialchars($alumno['Email_Alumno']); ?></span>
                            <input type="email" name="email" class="edit-input" value="<?php echo htmlspecialchars($alumno['Email_Alumno']); ?>" style="display:none;" disabled>
                        </div>
                    </div>
                    <div class="profile-field">
                        <label for="telefono">Número de contacto</label>
                        <div class="value" data-field="telefono">
                            <span class="display-value"><?php echo htmlspecialchars($alumno['Telefono']); ?></span>
                            <input type="text" name="telefono" class="edit-input" value="<?php echo htmlspecialchars($alumno['Telefono']); ?>" style="display:none;" disabled>
                        </div>
                    </div>
                </div>
                <div id="message-container" class="message-container"></div>
                <div class="profile-actions">
                    <button type="button" id="edit-btn" class="btn-edit"><i class="fas fa-pencil-alt"></i> Editar</button>
                    <button type="submit" id="save-btn" class="btn-save" style="display:none;"><i class="fas fa-save"></i> Guardar Cambios</button>
                    <button type="button" id="cancel-btn" class="btn-cancel" style="display:none;"><i class="fas fa-times"></i> Cancelar</button>
                </div>
            </form>
        </div>
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
                        <li><a href="../ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="../ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="../ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="../ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <br>
                        <li><a href="inscripciones.php">Inscripciones</a></li>
                        <br>
                        <li><a href="certificaciones.php">Certificaciones</a></li>
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
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../../JavaScript/general.js"></script>
    <script src="../../JavaScript/ALUMNO/perfil.js"></script>
    <script>
        fetch('../get_user_name.php')
            .then(response => response.json())
            .then(data => {
                const sessionControls = document.getElementById('session-controls');
                const mobileSessionSection = document.getElementById('mobile-session-section');
                
                if (data.user_name) {
                    let dropdownMenu;
                    let mobileSubmenu;

                    if (data.user_rol === 2) { // Estudiante
                        const userName = data.user_name;
                        dropdownMenu = `
                            <button class="user-menu-toggle">Hola, ${userName}. <i class="fas fa-chevron-down"></i></button>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="perfil.php">Mi Perfil</a></li>
                                    <li><a href="inscripciones.php">Inscripciones</a></li>
                                    <li><a href="certificaciones.php">Certificaciones</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        
                        mobileSubmenu = `
                            <a href="#" class="user-menu-toggle-mobile">Hola, ${userName} <i class="fas fa-chevron-down"></i></a>
                            <ul class="submenu">
                                <li><a href="perfil.php">Mi Perfil</a></li>
                                <li><a href="inscripciones.php">Inscripciones</a></li>
                                <li><a href="certificaciones.php">Certificaciones</a></li>
                                <li><a href="../logout.php">Cerrar Sesión</a></li>
                            </ul>`;

                    } else if (data.user_rol === 1) { // Admin
                        window.location.href = '../ADMIN/gestionar_inscriptos.php';
                        return; // No seguir ejecutando el script
                    }
                    
                    sessionControls.innerHTML = dropdownMenu;
                    if (mobileSessionSection) {
                        mobileSessionSection.innerHTML = mobileSubmenu;
                    }

                } else {
                    window.location.href = '../inicio_sesion.php?error=acceso_denegado';
                }
            });
    </script>
</body>
</html>