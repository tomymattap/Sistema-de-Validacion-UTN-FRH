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
    <link rel="stylesheet" href="../CSS/generar_certificado.css">
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

<main>
<div class="content-container">
<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Variables del formulario
    $id_curso = $_POST["id_curso"];
    $anio = $_POST["anio"];
    $cuatrimestre = $_POST["cuatrimestre"];
    $alumnos = $_POST["alumnos"];
    $id_admin = 'lione_29646'; // 🔹 Podés reemplazarlo luego por el admin logueado

    // Inicia una transacción
    mysqli_begin_transaction($conexion);

    try {
        foreach ($alumnos as $alumno) {
            if (!isset($alumno['cuil'])) continue; // solo los alumnos seleccionados

            $cuil = $alumno['cuil'];
            $estado = $alumno['estado'];

            // 1️⃣ INSERTA la certificación
            $insert = "
            INSERT INTO CERTIFICACION (
                Estado_Aprobacion,
                Fecha_Emision,
                ID_Admin,
                ID_CUV,
                ID_Inscripcion_Certif
            )
            SELECT 
                '$estado',
                CURDATE(),
                '$id_admin',
                CONCAT(
                    CASE WHEN c.Tipo = 'GENUINO' THEN 'G' ELSE 'C' END,
                    YEAR(CURDATE()),
                    LPAD(i.ID_Curso, 2, '0'),
                    LPAD(
                        COALESCE((
                            SELECT RIGHT(MAX(ID_CUV), 4) + 1
                            FROM CERTIFICACION ce
                            WHERE 
                                YEAR(ce.Fecha_Emision) = YEAR(CURDATE())
                                AND ce.ID_CUV LIKE CONCAT(
                                    CASE WHEN c.Tipo = 'GENUINO' THEN 'G' ELSE 'C' END,
                                    YEAR(CURDATE()),
                                    LPAD(i.ID_Curso, 2, '0'),
                                    '%'
                                )
                        ), 1),
                        4,
                        '0'
                    )
                ),
                i.ID_Inscripcion
            FROM INSCRIPCION i
            JOIN CURSO c ON c.ID_Curso = i.ID_Curso
            WHERE 
                i.ID_Cuil_Alumno = '$cuil'
                AND i.ID_Curso = '$id_curso'
                AND i.Anio = '$anio'
                AND i.Cuatrimestre = '$cuatrimestre'
                AND i.Estado_Cursada <> 'CERTIFICADA';

            ";

            if (!mysqli_query($conexion, $insert)) {
                throw new Exception("Error al generar certificación para $cuil: " . mysqli_error($conexion));
            }

            // 2️⃣ ACTUALIZA el estado de cursada
            $update = "
            UPDATE INSCRIPCION
            SET Estado_Cursada = 'CERTIFICADA'
            WHERE 
                ID_Cuil_Alumno = '$cuil'
                AND ID_Curso = '$id_curso'
                AND Anio = '$anio'
                AND Cuatrimestre = '$cuatrimestre';
            ";

            if (!mysqli_query($conexion, $update)) {
                throw new Exception("Error al actualizar estado para $cuil: " . mysqli_error($conexion));
            }

            echo "<div class='message success'>✅ Certificación generada para el alumno con CUIL $cuil ($estado)</div>";
        }

        // Si todo salió bien, confirma los cambios
        mysqli_commit($conexion);
        echo "<div class='message info'>🎉 Todas las certificaciones fueron generadas correctamente.</div>";

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        echo "<div class='message error'>❌ Ocurrió un error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }

    echo "
        <div class='button-container'>
            <a href='seleccionar_alum_certif.php' class='btn'>Volver a Emitir Certificados</a>
            <a href='descargar_certificados.php' class='btn'>Descargar Certificados Emitidos</a>
        </div>
";
}
?>
</div>
</main>

<footer class="site-footer">
        <!-- Footer content -->
</footer>

    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="../JavaScript/general.js"></script>
    <script src="../JavaScript/emitircertificados.js"></script>
    <a href="#" class="scroll-to-top-btn" title="Volver arriba"><i class="fas fa-arrow-up"></i></a>
</body>
</html>
