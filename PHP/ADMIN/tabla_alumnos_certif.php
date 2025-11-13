<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../inicio_sesion.php?error=acceso_denegado');
    exit;
}

include("../conexion.php");

$resultado = null; // Inicializar resultado

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que los datos esperados existen
    if (isset($_POST["curso"], $_POST["anio"], $_POST["cuatrimestre"])) {
        $curso_id = $_POST["curso"];
        $anio = $_POST["anio"];
        $cuatrimestre = $_POST["cuatrimestre"];

        // Consulta segura con sentencias preparadas para evitar inyección SQL
        $consulta = $conexion->prepare("
            SELECT a.ID_Cuil_Alumno, a.Nombre_Alumno, a.Apellido_Alumno, i.Comision
            FROM INSCRIPCION i
            JOIN ALUMNO a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
            WHERE i.ID_Curso = ? 
            AND i.Anio = ? 
            AND i.Cuatrimestre = ? 
            AND i.Estado_Cursada IN ('FINALIZADO', 'APROBADO')
        ");

    if (!$consulta) {
        die("Error en la preparación de la consulta: " . $conexion->error);
    }

        // "isss" indica que los tipos de datos son: integer, string, string, string
        $consulta->bind_param("iss", $curso_id, $anio, $cuatrimestre);
        $consulta->execute();
        $resultado = $consulta->get_result();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emitir Certificados - Admin</title>
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ADMIN/tabla_alumnos_certif.css">
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
                <a href="../../HTML/iniciosesion.html" class="login-btn">Iniciar Sesión</a>
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

    <main class="admin-section" style="padding-top: 4rem; padding-bottom: 4rem;">
        <div class="admin-container">
            <h1 class="main-title">Alumnos con posibilidad de certificacion</h1>

            <div class="results-container">
                <?php if ($resultado && $resultado->num_rows > 0): ?>



                    <form action="subir_archivos_certificado.php" method="POST">
                        <!-- campos ocultos para arrastrar datos -->
                        <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($_POST['curso']); ?>">
                        <input type="hidden" name="anio" value="<?php echo htmlspecialchars($_POST['anio']); ?>">
                        <input type="hidden" name="cuatrimestre" value="<?php echo htmlspecialchars($_POST['cuatrimestre']); ?>">
                        <table id="results-table" class="tabla-certificaciones">
                            <thead>
                                <tr>
                                    <th>Seleccionar</th>
                                    <th class="cuil-header">CUIL</th>
                                    <th class="nombre-header">Nombre</th>
                                    <th class="apellido-header">Apellido</th>
                                    <th class="comision-header">Comisión</th>
                                    <th class="estado-header">Tipo de Certificado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($fila = $resultado->fetch_assoc()): 
                                    $cuil = htmlspecialchars($fila['ID_Cuil_Alumno']);
                                ?>
                                    <tr>
                                        <td><input type="checkbox" name="alumnos[<?php echo $cuil; ?>][cuil]" value="<?php echo $cuil; ?>"></td>
                                        <td><?php echo $cuil; ?></td>
                                        <td><?php echo htmlspecialchars($fila['Nombre_Alumno']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['Apellido_Alumno']); ?></td>
                                        <td><?php echo htmlspecialchars($fila['Comision'] ?: 'Única'); ?></td>
                                        <td>
                                            <select name="alumnos[<?php echo $cuil; ?>][estado]">
                                                <option value="APROBADO" selected>APROBADO</option>
                                                <option value="ASISTIDO">ASISTIDO</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div class="form-buttons" style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 20px;">
                            <button type="button" class="btn-cancelar-cert" onclick="window.history.back()">Cancelar</button>
                            <button type="submit" class="btn-submit">Generar Certificaciones</button>
                        </div>
                    </form>




                <?php else: ?>
                    <div style="text-align: center; padding: 2rem 0;">
                        <p class="no-results" style="font-size: 1.1rem; margin-bottom: 2rem;">No se encontraron alumnos que cumplan con los criterios de búsqueda.</p>
                        <a href="seleccionar_alum_certif.php" class="back-btn">Volver</a>
                    </div>
                <?php endif; ?>
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
<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

<!-- ===================== FIN FOOTER ======================= -->

    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
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
                                    <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    } else {
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                    mobileNav.insertAdjacentHTML('beforeend', sessionHTML);
                } else {
                    window.location.href = '../inicio_sesion.php?error=session_expired';
                }
            });
    </script>   
</body>
</html>
