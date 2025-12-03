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

$id_evaluacion = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$evaluacion = null;
$error_message = '';
$success_message = '';

// --- MANEJO DE LA ACTUALIZACIÓN DE ESTADO (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_evaluacion_post = filter_input(INPUT_POST, 'id_evaluacion', FILTER_VALIDATE_INT);
    $nuevo_estado = $_POST['estado_evaluacion'] ?? '';

    if ($id_evaluacion_post && in_array($nuevo_estado, ['ACEPTADO', 'RECHAZADO'])) {
        $stmt = $conexion->prepare("UPDATE evaluacion_curso_externo SET Estado_Evaluacion = ? WHERE ID_Evaluacion = ?");
        $stmt->bind_param("si", $nuevo_estado, $id_evaluacion_post);
        if ($stmt->execute()) {
            $success_message = "El estado de la evaluación ha sido actualizado a '" . htmlspecialchars($nuevo_estado) . "'.";
        } else {
            $error_message = "Error al actualizar el estado.";
        }
        $stmt->close();
        // Asignamos el ID de GET para recargar los datos actualizados
        $id_evaluacion = $id_evaluacion_post;
    } else {
        $error_message = "Datos inválidos para la actualización.";
    }
}

// --- OBTENER DATOS PARA MOSTRAR (GET) ---
if (!$id_evaluacion) {
    $error_message = "No se proporcionó un ID de evaluación válido.";
} else {
    $consulta_sql = "
    SELECT 
        c.Nombre_Curso, 
        c.Modalidad, 
        c.Docente, 
        c.Carga_Horaria, 
        c.Descripcion, 
        c.Requisitos, 
        c.Categoria,
        e.ID_Evaluacion, 
        e.Instituciones_externas,
        e.Estado_Evaluacion, 
        e.Archivo_Evaluacion
    FROM evaluacion_curso_externo e
    JOIN curso c ON e.ID_Curso = c.ID_Curso
    WHERE e.ID_Evaluacion = ?
";

    $stmt = $conexion->prepare($consulta_sql);
    $stmt->bind_param("i", $id_evaluacion);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $evaluacion = $resultado->fetch_assoc();
    } else {
        $error_message = "No se encontró la evaluación solicitada.";
    }
    $stmt->close();
}

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Evaluación Externa</title>
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>ADMIN/gestionar_cursos.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>ADMIN/ver_evaluacion.css">
</head>
<body class="fade-in">

<main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
    <div class="gestion-cursos-container">
        <div class="contenido-principal">
            <div id="header-container">
                <h1 class="main-title">Detalle de Evaluación</h1>
                <a href="gestion_externos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
            </div>

            <?php if ($error_message): ?>
                <div class="mensaje error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                <div class="mensaje exito"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>

            <?php if ($evaluacion): ?>
                <div class="detalle-container">
                    <!-- Columna de Información -->
                    <div class="info-column">
                        <h2><?= htmlspecialchars($evaluacion['Nombre_Curso']) ?></h2>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Categoría</label>
                                <span><?= htmlspecialchars($evaluacion['Categoria']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Modalidad</label>
                                <span><?= htmlspecialchars($evaluacion['Modalidad']) ?></span>
                            </div>
                            <div class="info-item">
                                <label>Carga Horaria</label>
                                <span><?= htmlspecialchars($evaluacion['Carga_Horaria']) ?> hs</span>
                            </div>
                            <div class="info-item">
                                <label>Docente</label>
                                <span><?= htmlspecialchars($evaluacion['Docente'] ?: 'No especificado') ?></span>
                            </div>
                            <div class="info-item full-width">
                                <label>Descripción</label>
                                <p><?= nl2br(htmlspecialchars($evaluacion['Descripcion'])) ?></p>
                            </div>
                            <div class="info-item full-width">
                                <label>Requisitos</label>
                                <p><?= nl2br(htmlspecialchars($evaluacion['Requisitos'] ?: 'No especificados')) ?></p>
                            </div>
                        </div>

                        <h3>Instituciones Asociadas</h3>                        
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Instituciones Asociadas</label>
                                <span><?= nl2br(htmlspecialchars(str_replace(' - ', "\n", $evaluacion['Instituciones_externas']))) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Columna de Acción -->
                    <div class="action-column">
                        <h3>Evaluar Curso</h3>
                        <form action="ver_evaluacion_externa.php?id=<?= $id_evaluacion ?>" method="POST">
                            <input type="hidden" name="id_evaluacion" value="<?= $id_evaluacion ?>">
                            <div class="form-group">
                                <label for="estado_evaluacion">Estado de la Evaluación</label>
                                <select name="estado_evaluacion" id="estado_evaluacion">
                                    <option value="PENDIENTE" <?= $evaluacion['Estado_Evaluacion'] == 'PENDIENTE' ? 'selected' : '' ?>>Pendiente</option>
                                    <option value="ACEPTADO" <?= $evaluacion['Estado_Evaluacion'] == 'ACEPTADO' ? 'selected' : '' ?>>Aceptado</option>
                                    <option value="RECHAZADO" <?= $evaluacion['Estado_Evaluacion'] == 'RECHAZADO' ? 'selected' : '' ?>>Rechazado</option>
                                </select>
                            </div>
                            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Estado</button>
                        </form>

                        <hr>

                        <h3>Archivo Adjunto</h3>
                        <?php if (!empty($evaluacion['Archivo_Evaluacion'])): ?>
                            <a href="descargar_archivo_externo.php?id=<?= htmlspecialchars($evaluacion['ID_Evaluacion']) ?>" class="btn-descargar-pdf">
                                <i class="fas fa-file-download"></i> Descargar Archivo PDF
                            </a>
                        <?php else: ?>
                            <p class="sin-archivo">No se adjuntó ningún archivo para esta evaluación.</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<script src="<?php echo $js_path; ?>general.js"></script>
</body>
</html>
