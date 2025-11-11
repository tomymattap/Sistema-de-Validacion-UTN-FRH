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

require '../conexion.php';
$user_id = $_SESSION['user_id'];

// Consulta para obtener los certificados del alumno
$sql = "SELECT 
            c.Nombre_Curso, 
            cert.Estado_Aprobacion, 
            cert.Fecha_Emision,
            i.ID_Inscripcion,
            c.Tipo
        FROM certificacion cert
        JOIN inscripcion i ON cert.ID_Inscripcion_Certif = i.ID_Inscripcion
        JOIN curso c ON i.ID_Curso = c.ID_Curso
        WHERE i.ID_Cuil_Alumno = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$resultado = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificaciones - UTN FRH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/certificaciones.css">
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

    <!-- Menú Off-canvas -->
    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav>
            <ul>
                <li><a href="../../index.html">VALIDAR</a></li>
                <!--<li> <a href="../../HTML/cursos.html">CURSOS</a> </li>-->
                <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main>
        <div class="certificaciones-table-container">
            <h1>CERTIFICADOS</h1>
            <table class="certificaciones">
                <thead>
                    <tr>
                        <th>CURSO</th>
                        <th>ESTADO</th>
                        <th>FECHA DE EMISIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($resultado && $resultado->num_rows > 0): ?>
                        <?php while ($fila = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['Nombre_Curso']); ?></td>
                                <td><?php echo htmlspecialchars($fila['Estado_Aprobacion']); ?></td>
                                <td><?php echo htmlspecialchars(date("d/m/Y", strtotime($fila['Fecha_Emision']))); ?></td>
                                <td>
                                    <?php
                                    // Si el curso es 'Genuino', va a la encuesta. Si no, descarga directa.
                                    if ($fila['Tipo'] === 'Genuino') {
                                        $link = "ver_certificado.php?id=" . htmlspecialchars($fila['ID_Inscripcion']);
                                    } else {
                                        $link = "descargar_certificado.php?id=" . htmlspecialchars($fila['ID_Inscripcion']);
                                    }
                                    ?>
                                    <a href="<?php echo $link; ?>" class="action-btn">Ver Certificado</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
                    <li><a href="../../HTML/sobre_nosotros.html">Sobre Nosotros</a></li>
                    <li><a href="../../HTML/contacto.html">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-dynamic-nav" id="footer-dynamic-nav">
                <h4>Acceso</h4>
                <ul>
                    <li><a href="perfil.php">Mi Perfil</a></li>
                    <li><a href="inscripciones.php">Inscripciones</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </div>
        </div>
    </footer>

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
                        sessionHTML = `
                            <li><a href="perfil.php">Mi Perfil</a></li>
                            <li><a href="inscripciones.php">Inscripciones</a></li>
                            <li><a href="certificaciones.php">Certificaciones</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    } else if (data.user_rol === 1) { // Admin
                        // Redirigir si no es alumno
                        window.location.href = '../ADMIN/gestionar_inscriptos.php';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    // Redirigir si no está logueado
                    window.location.href = '../inicio_sesion.php?error=acceso_denegado';
                }

                // Añadir al menú móvil
                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>