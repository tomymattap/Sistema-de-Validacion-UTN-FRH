<?php
session_start();
require '../conexion.php';

// --- Security check for admin and request method ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    die("Acceso no permitido.");
}

$id_curso = filter_input(INPUT_POST, 'id_curso', FILTER_VALIDATE_INT);
if (!$id_curso) {
    die("ID de curso inválido.");
}

// --- Fetch course name for the filename ---
$curso_query = $conexion->prepare("SELECT Nombre_Curso FROM curso WHERE ID_Curso = ?");
$curso_query->bind_param("i", $id_curso);
$curso_query->execute();
$curso_result = $curso_query->get_result();
$curso_nombre = "encuestas"; // Default name
if ($curso_row = $curso_result->fetch_assoc()) {
    // Sanitize course name for filename
    $curso_nombre = preg_replace('/[^a-zA-Z0-9_ -]/', '', $curso_row['Nombre_Curso']);
    $curso_nombre = str_replace(' ', '_', $curso_nombre);
}
$curso_query->close();


// --- Fetch survey data ---
$sql = "SELECT
            a.Nombre_Alumno,
            a.Apellido_Alumno,
            a.ID_Cuil_Alumno,
            a.Email_Alumno,
            CASE es.desempeno_formador
                WHEN 'Malo' THEN 1
                WHEN 'Regular' THEN 2
                WHEN 'Bien' THEN 3
                WHEN 'Muy bien' THEN 4
            END AS desempeno_formador,
            CASE es.claridad_temas
                WHEN 'Malo' THEN 1
                WHEN 'Regular' THEN 2
                WHEN 'Bien' THEN 3
                WHEN 'Muy bien' THEN 4
            END AS claridad_temas,
            CASE es.ejemplos_practicos
                WHEN 'Malo' THEN 1
                WHEN 'Regular' THEN 2
                WHEN 'Bien' THEN 3
                WHEN 'Muy bien' THEN 4
            END AS ejemplos_practicos,
            CASE es.respuesta_dudas
                WHEN 'Malo' THEN 1
                WHEN 'Regular' THEN 2
                WHEN 'Bien' THEN 3
                WHEN 'Muy bien' THEN 4
            END AS respuesta_dudas,
            CASE es.cumplimiento_horarios
                WHEN 'Malo' THEN 1
                WHEN 'Regular' THEN 2
                WHEN 'Bien' THEN 3
                WHEN 'Muy bien' THEN 4
            END AS cumplimiento_horarios,
            es.satisfaccion_curso,
            es.contribucion_laboral,
            es.recomienda_frh,
            es.tema_no_hablado,
            es.sugerencias
        FROM
            encuesta_satisfaccion es
        JOIN
            inscripcion i ON es.ID_Inscripcion = i.ID_Inscripcion
        JOIN
            alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
        WHERE
            i.ID_Curso = ?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_curso);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $filename = "encuestas_" . $curso_nombre . "_" . date('Y-m-d') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // --- Añadir BOM para compatibilidad con Excel y UTF-8 ---
    fwrite($output, "\xEF\xBB\xBF");

    // --- Add headers to CSV ---
    fputcsv($output, [
        'Nombre Alumno',
        'Apellido Alumno',
        'CUIL',
        'Email Alumno',
        'Desempeño del Formador',
        'Claridad de los Temas',
        'Ejemplos Prácticos',
        'Respuesta a Dudas',
        'Cumplimiento de Horarios',
        'Satisfacción con el Curso (1-10)',
        'Contribución a la Formación Laboral (1-10)',
        'Recomienda UTN FRH',
        'Tema que faltó',
        'Sugerencias'
    ], ';');

    // --- Add data to CSV ---
    while ($fila = $resultado->fetch_assoc()) {
        fputcsv($output, $fila, ';');
    }

    fclose($output);
    exit();

} else {
    // No surveys found, redirect back with a message
    echo "<script>
            alert('No se encontraron encuestas para el curso seleccionado.');
            window.history.back();
          </script>";
}

$stmt->close();
$conexion->close();
?>