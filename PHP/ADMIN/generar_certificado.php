<?php
session_start();
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
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/generar_certificado.css">
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
                    <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
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

    <!-- Off-canvas Menu -->
    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav>
            <ul>
                <li><a href="../../index.html">VALIDAR</a></li>
                <!--<li> <a href="../../HTML/cursos.html">CURSOS</a> </li>-->
                <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

<main>
<div class="content-container">
<?php
include("../conexion.php");

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

    // Variables del formulario
    $id_curso = $_POST["id_curso"];
    $anio = $_POST["anio"];
    $cuatrimestre = $_POST["cuatrimestre"];
    $alumnos = $_POST["alumnos"];

    $alumnos_a_certificar = [];

    // Inicia una transacción
    mysqli_begin_transaction($conexion);

    try {
        foreach ($alumnos as $cuil => $alumno) {
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
                ) FROM INSCRIPCION i JOIN CURSO c ON i.ID_Curso = c.ID_Curso LEFT JOIN CERTIFICACION ce ON ce.ID_CUV LIKE CONCAT(CASE WHEN c.Tipo = 'GENUINO' THEN 'G' ELSE 'C' END, YEAR(CURDATE()), LPAD(i.ID_Curso, 2, '0'), '%') WHERE i.ID_Cuil_Alumno = ? AND i.ID_Curso = ? AND i.Anio = ? AND i.Cuatrimestre = ?),
                i.ID_Inscripcion
            FROM INSCRIPCION i
            WHERE i.ID_Cuil_Alumno = ? AND i.ID_Curso = ? AND i.Anio = ? AND i.Cuatrimestre = ? AND i.Estado_Cursada <> 'CERTIFICADA'
            ");
            $stmt_insert->bind_param("ssiiisiiis", $estado, $id_admin, $cuil, $id_curso, $anio, $cuatrimestre, $cuil, $id_curso, $anio, $cuatrimestre);

            if (!$stmt_insert->execute()) {
                throw new Exception("Error al generar certificación para $cuil: " . $stmt_insert->error);
            }

            // 2️⃣ ACTUALIZA el estado de cursada
            $stmt_update = $conexion->prepare("UPDATE INSCRIPCION SET Estado_Cursada = 'CERTIFICADA' WHERE ID_Cuil_Alumno = ? AND ID_Curso = ? AND Anio = ? AND Cuatrimestre = ?");
            $stmt_update->bind_param("siss", $cuil, $id_curso, $anio, $cuatrimestre);

            if (!$stmt_update->execute()) {
                throw new Exception("Error al actualizar estado para $cuil: " . $stmt_update->error);
            }

            echo "<div class='message success'>✅ Certificación generada para el alumno con CUIL $cuil ($estado)</div>";
        }

        // Si todo salió bien, confirma los cambios
        mysqli_commit($conexion);
        echo "<div class='message info'> Todas las certificaciones fueron generadas correctamente.</div>";

        // Guardar datos en la sesión para el siguiente paso
        $_SESSION['alumnos_para_certificar'] = $alumnos_a_certificar;
        $_SESSION['curso_info'] = [
            'id_curso' => $id_curso,
            'anio' => $anio,
            'cuatrimestre' => $cuatrimestre
        ];

        // Guardar info de archivos para el PDF
        $_SESSION['cert_files_info'] = [
            'firma_secretario_path' => $_POST['firma_secretario_path'] ?? null,
            'firma_docente_director_path' => $_POST['firma_docente_director_path'] ?? null,
            'logo_camara_path' => $_POST['logo_camara_path'] ?? null,
            'es_director' => isset($_POST['es_director']),
            'nombre_instituto' => $_POST['nombre_instituto'] ?? null
        ];

        // Mostrar botón de descarga
        echo "
            <div class='button-container'>
                <a href='seleccionar_alum_certif.php' class='btn'>Volver a Emitir Certificados</a>
                <a href='generar_pdf_certif.php' class='btn'>Descargar Certificados</a>
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
        <!-- Footer content -->
</footer>

    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="../../JavaScript/general.js"></script>
    <script src="../../JavaScript/emitircertificados.js"></script>
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
                                    <li><a href="gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                                    <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                                    <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    } else if (data.user_rol === 2) { // Alumno
                        // Redirigir si no es admin
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    // Redirigir si no está logueado
                    window.location.href = '../../PHP/iniciosesion.php';
                }

                // Añadir al menú móvil
                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>