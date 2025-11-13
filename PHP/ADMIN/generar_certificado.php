<?php
session_start();
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
    <link rel="stylesheet" href="../../CSS/ADMIN/generar_certificado.css">
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

    <!-- Off-canvas Menu -->
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

<main>
<div class="content-container">
<?php
include("../conexion.php");

// --- INCLUSIÓN DE LIBRERÍAS PHPMailer (directa, como en olvido_contrasena.php) ---
// Esto se hace para asegurar que PHPMailer se cargue correctamente en el entorno,
require_once(__DIR__ . '/../phpmailer/src/Exception.php');
require_once(__DIR__ . '/../phpmailer/src/PHPMailer.php');
require_once(__DIR__ . '/../phpmailer/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// Validar que solo los administradores puedan acceder y obtener su ID
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1 || !isset($_SESSION['user_id'])) {
    echo "<div class='message error'>❌ Acceso denegado. No tiene permiso para realizar esta acción.</div>";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Asegurarse de que el ID del admin esté en la sesión
    if (!isset($_SESSION['user_id'])) {
        die("<div class='message error'>❌ Su sesión ha expirado o es inválida. Por favor, inicie sesión de nuevo.</div>");
    }
    $id_admin = $_SESSION['user_id']; // Se obtiene el ID del admin logueado
    // ✅ Registrar el admin en MySQL para los triggers
    mysqli_query($conexion, "SET @current_admin = '$id_admin'");

    
    // Recuperar datos de archivos y campos de texto del POST
    // Estos datos vienen de los campos ocultos en subir_archivos_certificado.php
    $tipo_certificado = $_POST['tipo_certificado'] ?? '';
    $tipo_actividad = $_POST['tipo_actividad'] ?? '';
    $camara_organizadora = $_POST['camara_organizadora'] ?? '';
    $institutos_codictantes = $_POST['institutos_codictantes'] ?? '';
    $uploaded_files = $_POST['files'] ?? []; // Rutas de archivos temporales


    // Variables del formulario
    $id_curso = $_POST["id_curso"];
    $anio = $_POST["anio"];
    $cuatrimestre = $_POST["cuatrimestre"];

    $alumnos_a_certificar = [];

    // Inicia una transacción
    mysqli_begin_transaction($conexion);

    try {
        foreach ($_POST["alumnos"] as $cuil => $alumno) {
            if (!isset($alumno['cuil'])) continue; // solo los alumnos seleccionados

            $estado = $alumno['estado'];
            $alumnos_a_certificar[$cuil] = ['estado' => $estado, 'id_curso' => $id_curso]; // Guardar CUIL, estado e ID del curso para la sesión

            // 1️⃣ INSERTA la certificación
            $stmt_insert = $conexion->prepare("
            INSERT INTO CERTIFICACION (Estado_Aprobacion, Fecha_Emision, ID_Admin, ID_CUV, ID_Inscripcion_Certif)
            SELECT ?, CURDATE(), ?, 
                (SELECT CONCAT(
                    CASE WHEN c.Tipo = 'GENUINO' THEN 'G' ELSE 'C' END,
                    YEAR(CURDATE()),
                    LPAD(i.ID_Curso, 2, '0'),
                    LPAD(COALESCE(MAX(CAST(SUBSTRING(ce.ID_CUV, 8) AS UNSIGNED)), 0) + 1, 4, '0') 
                ) FROM INSCRIPCION i JOIN CURSO c ON i.ID_Curso = c.ID_Curso LEFT JOIN CERTIFICACION ce ON ce.ID_CUV LIKE CONCAT(CASE WHEN c.Tipo = 'GENUINO' THEN 'G' ELSE 'C' END, YEAR(CURDATE()), LPAD(i.ID_Curso, 2, '0'), '%') WHERE i.ID_Cuil_Alumno = ? AND i.ID_Curso = ? AND i.Anio = ?),
                i.ID_Inscripcion
            FROM INSCRIPCION i
            WHERE i.ID_Cuil_Alumno = ? AND i.ID_Curso = ? AND i.Anio = ? AND i.Estado_Cursada <> 'CERTIFICADA'
            ");
            $stmt_insert->bind_param("ssiiisii", $estado, $id_admin, $cuil, $id_curso, $anio, $cuil, $id_curso, $anio);

            if (!$stmt_insert->execute()) {
                throw new Exception("Error al generar certificación para $cuil: " . $stmt_insert->error);
            }

            // 2️⃣ ACTUALIZA el estado de cursada
            $stmt_update = $conexion->prepare("UPDATE INSCRIPCION SET Estado_Cursada = 'CERTIFICADA' WHERE ID_Cuil_Alumno = ? AND ID_Curso = ? AND Anio = ?");
            $stmt_update->bind_param("sii", $cuil, $id_curso, $anio);

            if (!$stmt_update->execute()) {
                throw new Exception("Error al actualizar estado para $cuil: " . $stmt_update->error);
            }

            echo "<div class='message success'>✅ Certificación generada para el alumno con CUIL $cuil ($estado)</div>";

            // --- INICIO: ENVÍO DE EMAIL DE NOTIFICACIÓN ---
            $query_email = $conexion->prepare("SELECT Nombre_Alumno, Apellido_Alumno, Email_Alumno FROM ALUMNO WHERE ID_Cuil_Alumno = ?");
            $query_email->bind_param("s", $cuil);
            $query_email->execute();
            $alumno_data = $query_email->get_result()->fetch_assoc();
            $query_email->close();

            // Obtener el nombre del curso para el email
            $query_curso = $conexion->prepare("SELECT Nombre_Curso FROM CURSO WHERE ID_Curso = ?");
            $query_curso->bind_param("i", $id_curso);
            $query_curso->execute();
            $curso_data = $query_curso->get_result()->fetch_assoc();
            $nombre_curso = $curso_data['Nombre_Curso'] ?? 'Curso no especificado';

            if ($alumno_data && !empty($alumno_data['Email_Alumno'])) {
                $mail = new PHPMailer(true);
                try {
                    // Configuración del servidor SMTP
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'sollione2004@gmail.com'; // Cambiar al mail de la secretaria
                    $mail->Password   = 'masu hqty zqfc pudz';      // Tu contraseña de aplicación de Gmail
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    // Remitente y destinatario
                    $mail->setFrom('sollione2004@gmail.com', 'Sistema de Validacion UTN FRH');
                    $mail->addAddress($alumno_data['Email_Alumno'], $alumno_data['Nombre_Alumno'] . ' ' . $alumno_data['Apellido_Alumno']);

                    // Contenido del mail
                    $mail->isHTML(true);
                    $mail->Subject = 'Tu certificado del curso esta listo para descargar';
                    $mail->CharSet = 'UTF-8'; // ¡Esta es la línea clave!
                    $login_link = "http://{$_SERVER['HTTP_HOST']}/Sistema-De-Validacion-UTN-FRH/PHP/inicio_sesion.php";
                    $mail->Body    = "Hola " . htmlspecialchars($alumno_data['Nombre_Alumno']) . ",<br><br>Te informamos que tu certificado para el curso <strong>\"" . htmlspecialchars($nombre_curso) . "\"</strong> ya se encuentra disponible en tu perfil.<br><br>Puedes acceder a la plataforma para descargarlo haciendo clic en el siguiente enlace:<br><a href='$login_link'>Iniciar Sesión y ver mis certificados</a><br><br>Saludos,<br>Equipo de Extensión Universitaria - UTN FRH.";

                    $mail->send();
                    echo "<div class='message success' style='background-color: #e6f7ff; border-color: #91d5ff; color: #0050b3;'>📧 Notificación enviada a " . htmlspecialchars($alumno_data['Email_Alumno']) . "</div>";
                } catch (Exception $e) {
                    echo "<div class='message error' style='background-color: #fffbe6; border-color: #ffe58f; color: #d46b08;'>⚠️ No se pudo enviar la notificación por correo al alumno con CUIL $cuil. Mailer Error: {$mail->ErrorInfo}</div>";
                }
            }
            // --- FIN: ENVÍO DE EMAIL DE NOTIFICACIÓN ---
        }

        // Si todo salió bien, confirma los cambios
        mysqli_commit($conexion);
        mysqli_close($conexion); // 🔹 Cierra la conexión y fuerza el flush del commit
        sleep(1);                // 🔹 Espera un segundo para asegurar que el commit se propague
        echo "<div class='message info'>Todas las certificaciones fueron generadas correctamente.</div>";

        // Guardar datos en la sesión para generar_pdf_certif.php
        $_SESSION['alumnos_para_certificar'] = $alumnos_a_certificar;
        $_SESSION['curso_info'] = [
            'id_curso' => $id_curso,
            'anio' => $anio,
            'cuatrimestre' => $cuatrimestre
        ];
        // Reconstruir cert_data_for_pdf desde el POST y guardarlo en sesión
        $_SESSION['cert_data_for_pdf'] = [
            'tipo_certificado' => $tipo_certificado,
            'tipo_actividad' => $tipo_actividad,
            'camara_organizadora' => $camara_organizadora,
            'institutos_codictantes' => $institutos_codictantes,
            'files' => $uploaded_files
        ];

        // Mostrar botones de acción
        echo "
            <div class='button-container'>
                <a href='seleccionar_alum_certif.php' class='btn'>Volver a Emitir Certificados</a>
                <a href='generar_pdf_certif.php' class='btn' target='_blank'>Descargar Certificados</a>
            </div>
        ";
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        echo "<div class='message error'>❌ Ocurrió un error: " . htmlspecialchars($e->getMessage()) . "</div>";
        echo "<div class='button-container'><a href='seleccionar_alum_certif.php' class='btn'>Volver</a></div>";
    }
}
?>
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

    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="../../JavaScript/general.js"></script>
    <script src="../../JavaScript/ADMIN/emitir_certificados.js"></script>
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
                    } else if (data.user_rol === 2) { // Alumno
                        // Redirigir si no es admin
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    // Redirigir si no está logueado o la sesión expiró
                    window.location.href = '../inicio_sesion.php?error=session_expired';
                }

                // Añadir al menú móvil
                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>