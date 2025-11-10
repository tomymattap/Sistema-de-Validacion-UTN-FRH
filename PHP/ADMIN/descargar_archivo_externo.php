<?php
include("../conexion.php");

// Verificar que se reciba el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: Falta el parámetro ID.");
}

$id_evaluacion = intval($_GET['id']);

// Buscar el archivo en la base de datos
$sql = "SELECT Archivo_Evaluacion FROM evaluacion_curso_externo WHERE ID_Evaluacion = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_evaluacion);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    die("No se encontró el archivo solicitado.");
}

mysqli_stmt_bind_result($stmt, $archivo);
mysqli_stmt_fetch($stmt);

// Enviar encabezados HTTP para descargar el PDF
header("Content-Type: application/pdf");
header("Content-Disposition: attachment; filename=certificacion_" . $id_evaluacion . ".pdf");
header("Content-Length: " . strlen($archivo));

echo $archivo;

exit;
?>
