<?php
session_start();
include("../conexion.php");

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../inicio_sesion.php?error=acceso_denegado');
    exit;
}

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
$current_page = 'cambiar_contrasena_obligatorio.php';

$cursos_modificados = [];
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cursos'])) {
    $id_admin = $_SESSION['user_id'];
    // ✅ Registrar el admin en MySQL para los triggers
    mysqli_query($conexion, "SET @current_admin = '$id_admin'");
    
    $cursos_post = $_POST['cursos'];

    // Iniciar transacción para la actualización
    mysqli_begin_transaction($conexion);
    try {
        // --- VALIDACIÓN DE FECHAS (solo para fechas modificadas) ---
        $fecha_actual = date('Y-m-d');
        foreach ($cursos_post as $id_curso => $datos) {
            $id_curso_int = intval($id_curso);
            $nombre_curso = $datos['nombre'];
            $inicio_nuevo = !empty($datos['inicio']) ? $datos['inicio'] : null;
            $fin_nuevo = !empty($datos['fin']) ? $datos['fin'] : null;

            // Obtener fechas actuales para comparar
            $stmt_check = mysqli_prepare($conexion, "SELECT Inicio_Curso, Fin_Curso FROM duracion_curso WHERE ID_Curso = ?");
            mysqli_stmt_bind_param($stmt_check, "i", $id_curso_int);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $fechas_actuales = mysqli_fetch_assoc($result_check);
            mysqli_stmt_close($stmt_check);

            $inicio_actual = $fechas_actuales['Inicio_Curso'] ?? null;
            $fin_actual = $fechas_actuales['Fin_Curso'] ?? null;

            // Solo validar si la fecha de inicio fue modificada
            if ($inicio_nuevo !== $inicio_actual) {
                if ($inicio_nuevo && $inicio_nuevo < $fecha_actual) {
                    throw new Exception("La fecha de inicio para el curso '" . htmlspecialchars($nombre_curso) . "' no puede ser anterior a la fecha actual.");
                }
            }

            // Validaciones de coherencia si hay fechas nuevas
            if ($fin_nuevo && !$inicio_nuevo) {
                throw new Exception("Para el curso '" . htmlspecialchars($nombre_curso) . "', si se define una fecha de fin, la fecha de inicio es obligatoria.");
            }
            if ($inicio_nuevo && $fin_nuevo && $fin_nuevo < $inicio_nuevo) {
                throw new Exception("La fecha de fin para el curso '" . htmlspecialchars($nombre_curso) . "' no puede ser anterior a su fecha de inicio.");
            }
        }

        foreach ($cursos_post as $id_curso => $datos) {
            $id_curso = intval($id_curso);
            $nombre_curso = $datos['nombre'];
            $inicio_nuevo = !empty($datos['inicio']) ? $datos['inicio'] : null;
            $fin_nuevo = !empty($datos['fin']) ? $datos['fin'] : null;

            // Obtener fechas actuales para comparar
            $stmt_check = mysqli_prepare($conexion, "SELECT Inicio_Curso, Fin_Curso FROM duracion_curso WHERE ID_Curso = ?");
            mysqli_stmt_bind_param($stmt_check, "i", $id_curso_int);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $fechas_actuales = mysqli_fetch_assoc($result_check);
            mysqli_stmt_close($stmt_check);


            $inicio_actual = $fechas_actuales['Inicio_Curso'] ?? null;
            $fin_actual = $fechas_actuales['Fin_Curso'] ?? null;

            // Solo procesar si hay un cambio real
            if ($inicio_nuevo !== $inicio_actual || $fin_nuevo !== $fin_actual) {
                if ($fechas_actuales) {
                    // Actualizar si existe
                    $sql = "UPDATE duracion_curso SET Inicio_Curso = ?, Fin_Curso = ? WHERE ID_Curso = ?";
                    $stmt = mysqli_prepare($conexion, $sql);
                    mysqli_stmt_bind_param($stmt, "ssi", $inicio_nuevo, $fin_nuevo, $id_curso);
                } else {
                    // Insertar si no existe
                    $sql = "INSERT INTO duracion_curso (ID_Curso, Inicio_Curso, Fin_Curso, Horario) VALUES (?, ?, ?, 'A definir')";
                    $stmt = mysqli_prepare($conexion, $sql);
                    mysqli_stmt_bind_param($stmt, "iss", $id_curso, $inicio_nuevo, $fin_nuevo);
                }

                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Error al procesar el curso ID $id_curso: " . mysqli_stmt_error($stmt));
                }

                // Añadir a la lista de modificados para mostrar en el resumen
                $cursos_modificados[] = [
                    'id' => $id_curso,
                    'nombre' => $nombre_curso,
                    'inicio' => $inicio_nuevo,
                    'fin' => $fin_nuevo
                ];
            }
        }
        mysqli_commit($conexion);
    } catch (Exception $e) {
        mysqli_rollback($conexion);
        $error_message = $e->getMessage();
    }
} else {
    // Si se accede sin POST, redirigir
    header('Location: editar_duracion_cursos.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Cambios - Admin</title>
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_cursos.css">
</head>
<body class="fade-in">
    <header class="site-header">
        <div class="header-container">
            <div class="logo"><a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a></div>
        </div>
    </header>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <div class="contenido-principal">

                <?php if ($error_message): ?>
                    <div class="confirmacion-container" style="border-left-color: var(--color-secundario-4);">
                        <div class="confirmacion-header">
                            <i class="fas fa-times-circle" style="color: var(--color-secundario-4);"></i>
                            <h3>Error al actualizar</h3>
                        </div>
                        <p><?= htmlspecialchars($error_message) ?></p>
                        <a href="editar_duracion_cursos.php" class="menu-btn" style="margin-top: 1rem;"><i class="fas fa-arrow-left"></i> Volver a Intentar</a>
                    </div>
                <?php elseif (!empty($cursos_modificados)): ?>
                    <div class="confirmacion-container">
                        <div class="confirmacion-header">
                            <i class="fas fa-check-circle"></i>
                            <h3>Fechas actualizadas correctamente</h3>
                        </div>
                        <p>Se han guardado los cambios para los siguientes cursos:</p>
                        <ul class="lista-confirmacion">
                            <?php foreach ($cursos_modificados as $curso): ?>
                                <li>
                                    <strong><?= htmlspecialchars($curso['nombre']) ?> (ID: <?= htmlspecialchars($curso['id']) ?>)</strong>
                                    <div class="fechas-confirmacion">
                                        <span>Inicio: <strong><?= htmlspecialchars($curso['inicio'] ?: 'No asignada') ?></strong></span>
                                        <span>Fin: <strong><?= htmlspecialchars($curso['fin'] ?: 'No asignada') ?></strong></span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php else: ?>
                     <div class="confirmacion-container" style="border-left-color: var(--color-secundario-3);">
                        <div class="confirmacion-header">
                            <i class="fas fa-info-circle" style="color: var(--color-secundario-3);"></i>
                            <h3>No se realizaron cambios</h3>
                        </div>
                        <p>No se detectaron modificaciones en las fechas de ningún curso.</p>
                    </div>
                <?php endif; ?>
                    
                <div class="form-actions" style="text-align: center; margin-top: 2rem;">
                    <a href="gestionar_cursos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> Volver a Gestión de Cursos</a>
                </div>

            </div>
        </div>
    </main>

    <footer class="site-footer"></footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../../JavaScript/general.js"></script>
</body>
</html>