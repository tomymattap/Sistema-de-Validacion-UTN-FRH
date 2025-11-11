<?php
include("conexion.php");

// Recibir código desde el formulario (por POST o GET)
$codigo = $_POST['codigo'] ?? $_GET['codigo'] ?? '';
$datos = null;
$mensajeError = '';

if (!empty($codigo)) {
    $consulta = "
        SELECT 
            cr.id_cuv,
            cr.Estado_Aprobacion,
            CONCAT(a.Nombre_Alumno, ' ', a.Apellido_Alumno) AS Nombre,
            a.DNI_Alumno,
            c.Nombre_Curso,
            cr.Fecha_Emision  
        FROM certificacion cr 
        JOIN inscripcion i ON i.ID_Inscripcion = cr.ID_Inscripcion_Certif
        JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
        JOIN curso c ON c.ID_Curso = i.ID_Curso
        WHERE cr.ID_CUV = '$codigo';
    ";

    $resultado = mysqli_query($conexion, $consulta);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $datos = mysqli_fetch_assoc($resultado);
    } else {
        $mensajeError = "❌ No se encontró ningún certificado con ese código.";
    }
} else {
    $mensajeError = "⚠️ No se ingresó ningún código de certificado.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado de Validación - UTN FRH</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/INICIO/validacion.css">
    
</head>
<body>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../index.html"><img src="../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.html">VALIDAR</a></li>
                    <!--<li> <a href="../HTML/cursos.html">CURSOS</a> </li>-->
                    <li><a href="../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../HTML/contacto.html">CONTACTO</a></li>
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
                <li><a href="../index.html">VALIDAR</a></li>
                <!--<li> <a href="../HTML/cursos.html">CURSOS</a> </li>-->
                <li><a href="../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main class="validation-page">
        <div class="validation-result-container">
            
            <?php if ($mensajeError): ?>
                <div class="result-body">
                    <div class="error-msg"><?= $mensajeError ?></div>
            <?php elseif ($datos): ?>
                <div class="result-header">
                    <h2>Resultado de la validación del certificado</h2>
                </div>
                <div class="result-body">

                    <p class="valid-message"> El código ingresado pertenece a un certificado <strong>VÁLIDO</strong> emitido por la Universidad Tecnológica Nacional, Facultad Regional Haedo.</p>
                    <hr>

                    <div class="certificate-data">
                        <p><strong>Código:</strong> <span id="cert-codigo"><?= htmlspecialchars($datos['id_cuv']) ?></span></p>
                        <p><strong>Tipo de Certificado:</strong> <span id="cert-tipo"><?= htmlspecialchars($datos['Estado_Aprobacion']) ?></span></p>
                        <p><strong>Nombre:</strong> <span id="cert-nombre"><?= htmlspecialchars($datos['Nombre']) ?></span></p>
                        <p><strong>Documento:</strong> <span id="cert-dni"><?= htmlspecialchars($datos['DNI_Alumno']) ?></span></p>
                        <p><strong>Curso:</strong> <span id="cert-curso"><?= htmlspecialchars($datos['Nombre_Curso']) ?></span></p>
                        <p><strong>Fecha de Emisión:</strong> <span id="cert-fecha"><?= htmlspecialchars($datos['Fecha_Emision']) ?></span></p>
                    </div>
                    <br><br>
                    <p class="validation-text">
                        Se hace constar que <strong><?= htmlspecialchars($datos['Nombre']) ?></strong>, 
                        DNI <?= htmlspecialchars($datos['DNI_Alumno']) ?>, ha cursado y 
                        <?= strtolower($datos['Estado_Aprobacion']) === 'aprobado' ? 'aprobado' : 'asistido' ?>
                        el curso <strong><?= htmlspecialchars($datos['Nombre_Curso']) ?></strong>, 
                        organizado por la UNIVERSIDAD TECNOLÓGICA NACIONAL.
                    </p>
                <?php endif; ?>
                <hr>
                <p class="contact-info">Ante cualquier duda, enviar un correo a <a href="mailto:extension@frh.utn.edu.ar">extension@frh.utn.edu.ar</a></p>
            </div>
            <?php if (!$mensajeError): ?></div><?php endif; ?>
        </div>
        <div class="actions-container">
            <a href="../index.html" class="back-btn">Volver</a>
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
                    <!-- <li><a href="../HTML/cursos.html">Cursos</a></li> -->
                    <li><a href="../HTML/sobre_nosotros.html">Sobre Nosotros</a></li>
                    <li><a href="../HTML/contacto.html">Contacto</a></li>
                </ul>
            </div>
            <div class="footer-separator"></div>
            <div class="footer-dynamic-nav" id="footer-dynamic-nav">
            </div>
        </div>
    </footer>

    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="../JavaScript/inicio.js"></script>
    <script src="../JavaScript/general.js"></script>
    <script>
        fetch('get_user_name.php')
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
                                    <li><a href="ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                    <li><a href="ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                                    <li><a href="ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                    <li><a href="ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                                    <li><a href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="logout.php">Cerrar Sesión</a></li>`;
                    } else if (data.user_rol === 2) { // Alumno
                        dropdownMenu = `
                            <button class="user-menu-toggle">Hola, ${data.user_name}. <i class="fas fa-chevron-down"></i></button>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="ALUMNO/perfil.php">Mi Perfil</a></li>
                                    <li><a href="ALUMNO/inscripciones.php">Inscripciones</a></li>
                                    <li><a href="ALUMNO/certificaciones.php">Certificaciones</a></li>
                                    <li><a href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="ALUMNO/perfil.php">Mi Perfil</a></li>
                            <li><a href="ALUMNO/inscripciones.php">Inscripciones</a></li>
                            <li><a href="ALUMNO/certificaciones.php">Certificaciones</a></li>
                            <li><a href="logout.php">Cerrar Sesión</a></li>`;
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    sessionControls.innerHTML = '<a href="inicio_sesion.php" class="login-btn">INICIAR SESIÓN</a>';
                    sessionHTML = '<li><a href="inicio_sesion.php">INICIAR SESIÓN</a></li>';
                }

                // Añadir al menú móvil
                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>