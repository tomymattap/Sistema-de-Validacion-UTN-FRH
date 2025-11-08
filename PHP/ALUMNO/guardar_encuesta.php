<?php
session_start();
require '../conexion.php';

// --- BLOQUES DE SEGURIDAD ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Acceso no permitido.");
}
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    die("Acceso denegado.");
}

$id_inscripcion = filter_input(INPUT_POST, 'id_inscripcion', FILTER_VALIDATE_INT);
if (!$id_inscripcion) {
    die("ID de inscripción inválido.");
}

// --- RECOLECCIÓN DE DATOS DEL FORMULARIO ---
$desempeno_formador = $_POST['desempeno_formador'] ?? '';
$claridad_temas = $_POST['claridad_temas'] ?? '';
$ejemplos_practicos = $_POST['ejemplos_practicos'] ?? '';
$respuesta_dudas = $_POST['respuesta_dudas'] ?? '';
$cumplimiento_horarios = $_POST['cumplimiento_horarios'] ?? '';
$satisfaccion_curso = filter_input(INPUT_POST, 'satisfaccion_curso', FILTER_VALIDATE_INT);
$contribucion_laboral = filter_input(INPUT_POST, 'contribucion_laboral', FILTER_VALIDATE_INT);
$recomienda_frh = $_POST['recomienda_frh'] ?? '';
$tema_no_hablado = $_POST['tema_no_hablado'] ?? NULL;
$sugerencias = $_POST['sugerencias'] ?? NULL;

// --- VALIDACIÓN DE DATOS REQUERIDOS ---
if (empty($desempeno_formador) || empty($claridad_temas) || empty($ejemplos_practicos) || empty($respuesta_dudas) || empty($cumplimiento_horarios) || $satisfaccion_curso === false || $contribucion_laboral === false || empty($recomienda_frh)) {
    die("Error: Faltan respuestas obligatorias.");
}

// --- INSERCIÓN EN LA BASE DE DATOS ---
$sql = "INSERT INTO encuesta_satisfaccion (ID_Inscripcion, desempeno_formador, claridad_temas, ejemplos_practicos, respuesta_dudas, cumplimiento_horarios, satisfaccion_curso, contribucion_laboral, recomienda_frh, tema_no_hablado, sugerencias) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("isssssiisss", $id_inscripcion, $desempeno_formador, $claridad_temas, $ejemplos_practicos, $respuesta_dudas, $cumplimiento_horarios, $satisfaccion_curso, $contribucion_laboral, $recomienda_frh, $tema_no_hablado, $sugerencias);

if ($stmt->execute()) {
    // Redirigir de nuevo a la página de ver_certificado para que ahora muestre el botón de descarga
    header("Location: ver_certificado.php?id=" . $id_inscripcion);
    exit();
} else {
    die("Error al guardar la encuesta: " . $stmt->error);
}

$stmt->close();
$conexion->close();
?>