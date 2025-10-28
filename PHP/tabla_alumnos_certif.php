<?php
include("conexion.php");

$resultado = null; // Inicializar resultado

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar que los datos esperados existen
    if (isset($_POST["curso"], $_POST["anio"], $_POST["cuatrimestre"])) {
        $curso_id = $_POST["curso"];
        $anio = $_POST["anio"];
        $cuatrimestre = $_POST["cuatrimestre"];

        // Consulta segura con sentencias preparadas para evitar inyección SQL
        $consulta = $conexion->prepare("
            SELECT a.ID_Cuil_Alumno, a.Nombre_Alumno, a.Apellido_Alumno
            FROM INSCRIPCION i
            JOIN ALUMNO a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
            WHERE i.ID_Curso = ?
            AND i.Anio = ?
            AND i.Cuatrimestre = ?
            AND i.Estado_Cursada = 'FINALIZADO'
        ");
        // "iss" indica que los tipos de datos son: integer, string, string
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/verinscriptos.css">  <!-- estilos de la tabla -->
    <link rel="stylesheet" href="../CSS/tabla_alumnos_certif.css">
    <link rel="stylesheet" href="../CSS/validacion.css">
</head>
<body class="fade-in">
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../index.html"><img src="../Imagenes/UTNLogo.webp" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.html">VALIDAR</a></li>
                    <!--<li> <a href="HTML/cursos.html">CURSOS</a> </li>-->
                    <li><a href="sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <button class="user-menu-toggle">Hola, Admin. <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <ul>
                        <li><a href="../HTML/verinscriptos.html">Ver Inscriptos</a></li>
                        <li><a href="../HTML/gestionarcursos.html">Gestionar Cursos</a></li>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <li><a href="#">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        <div class="mobile-nav">
            <button class="close-menu" aria-label="Cerrar menú">&times;</button>
            <nav>
                <ul>
                    <li><a href="../index.html">INICIO</a></li>
                    <li><a href="cursos.html">CURSOS</a></li>
                    <li><a href="sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="contacto.html">CONTACTO</a></li>
                </ul>
                <div class="mobile-session-controls" id="mobile-session-controls">
                    <!-- Contenido dinámico por JS -->
                </div>
            </nav>
        </div>
    </header>

    <main class="admin-section" style="padding-top: 4rem; padding-bottom: 4rem;">
        <div class="admin-container">
            <h1 class="main-title">Alumnos con posibilidad de certificacion</h1>

            <div class="results-container">
                <?php if ($resultado && $resultado->num_rows > 0): ?>



                    <form action="generar_certificado.php" method="POST">
                        <!-- 🔹 campos ocultos para arrastrar datos -->
                        <input type="hidden" name="id_curso" value="<?php echo htmlspecialchars($_POST['curso']); ?>">
                        <input type="hidden" name="anio" value="<?php echo htmlspecialchars($_POST['anio']); ?>">
                        <input type="hidden" name="cuatrimestre" value="<?php echo htmlspecialchars($_POST['cuatrimestre']); ?>">

                        <table id="results-table">
                            <thead>
                                <tr>
                                    <th>Seleccionar</th>
                                    <th class="cuil-header">CUIL</th>
                                    <th class="nombre-header">Nombre</th>
                                    <th class="apellido-header">Apellido</th>
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
                        <div class="form-buttons" style="text-align: right; margin-top: 20px;">
                            <button type="submit" class="submit-btn">Generar Certificaciones</button>
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
                    <li><a href="../index.html">Inicio</a></li>
                    <li><a href="cursos.html">Cursos</a></li>
                    <li><a href="sobrenosotros.html">Sobre Nosotros</a></li>
                    <li><a href="contacto.html">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-dynamic-nav" id="footer-dynamic-nav">
                <h4>Acceso</h4>
                <ul>
                    <li><a href="verinscriptos.html">Ver Inscriptos</a></li>
                    <li><a href="gestionarcursos.html">Gestionar Cursos</a></li>
                    <li><a href="emitircertificados.html">Emitir Certificados</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="../JavaScript/general.js"></script>
    <a href="#" class="scroll-to-top-btn" title="Volver arriba"><i class="fas fa-arrow-up"></i></a>
</body>
</html>
