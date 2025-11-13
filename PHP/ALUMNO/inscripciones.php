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
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
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
                <?php
                // Incluir el archivo de conexión
                include '../conexion.php';

                // Obtener el ID del alumno de la sesión
                if (isset($_SESSION['user_id'])) {
                    $id_alumno = $_SESSION['user_id'];

                    // Consulta para obtener los cursos en los que el alumno está inscripto
                    $sql = "SELECT 
                                c.ID_Curso,
                                c.Nombre_Curso, 
                                c.Modalidad, 
                                c.Docente,
                                dc.Inicio_Curso, 
                                dc.Fin_Curso,
                                i.Estado_Cursada
                            FROM inscripcion i
                            JOIN curso c ON i.ID_Curso = c.ID_Curso
                            LEFT JOIN duracion_curso dc ON c.ID_Curso = dc.ID_Curso
                            WHERE i.ID_Cuil_Alumno = ?";

                    $stmt = $conexion->prepare($sql);
                    
                    if ($stmt) {
                        $stmt->bind_param("i", $id_alumno);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $estado_cursada = strtolower($row['Estado_Cursada']);
                                $estado_js = '';
                                switch ($estado_cursada) {
                                    case 'pendiente':
                                        $estado_js = 'pendiente';
                                        break;
                                    case 'en curso':
                                        $estado_js = 'aceptado';
                                        break;
                                    case 'finalizado':
                                    case 'certificada':
                                        $estado_js = 'finalizado';
                                        break;
                                    default:
                                        $estado_js = 'pendiente'; // O un estado por defecto
                                }

                                echo '<div class="curso-item" data-course-id="' . htmlspecialchars($row['ID_Curso']) . '" data-estado="' . $estado_js . '">';
                                echo '    <div class="curso-header">';
                                echo '        <h3>' . htmlspecialchars($row['Nombre_Curso']) . '</h3>';
                                echo '    </div>';
                                echo '    <div class="curso-details">';
                                echo '        <p><strong>Fecha de inicio:</strong> ' . ($row['Inicio_Curso'] ? htmlspecialchars(date("d/m/Y", strtotime($row['Inicio_Curso']))) : 'No especificada') . '</p>';
                                echo '        <p><strong>Fecha de finalización:</strong> ' . ($row['Fin_Curso'] ? htmlspecialchars(date("d/m/Y", strtotime($row['Fin_Curso']))) : 'No especificada') . '</p>';
                                echo '        <p><strong>Modalidad:</strong> ' . htmlspecialchars($row['Modalidad']) . '</p>';
                                echo '        <p><strong>Docente a Cargo:</strong> ' . ($row['Docente'] ? htmlspecialchars($row['Docente']) : 'A confirmar') . '</p>';
                                echo '        <p><strong>Estado:</strong> ' . htmlspecialchars($row['Estado_Cursada']) . '</p>';
                                echo '    </div>';
                                echo '</div>';
                            }
                        } else {
                            echo '<p class="no-cursos">Aún no te has inscripto a ningún curso.</p>';
                        }
                        $stmt->close();
                    } else {
                        echo '<p class="no-cursos">Error al preparar la consulta.</p>';
                    }
                    $conexion->close();
                } else {
                    echo '<p class="no-cursos">No se pudo identificar al usuario.</p>';
                }
                ?>
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