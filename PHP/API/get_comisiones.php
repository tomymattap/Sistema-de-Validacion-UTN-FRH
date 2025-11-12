<?php
header('Content-Type: application/json; charset=utf-8');
include("../conexion.php");

$curso_id = filter_input(INPUT_GET, 'curso_id', FILTER_VALIDATE_INT);

if (!$curso_id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de curso no válido.']);
    exit;
}

$sql = "SELECT DISTINCT Comision 
        FROM inscripcion 
        WHERE ID_Curso = ? 
        AND Comision IS NOT NULL 
        AND Comision != '' 
        ORDER BY Comision ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $curso_id);
$stmt->execute();
$resultado = $stmt->get_result();
$comisiones = $resultado->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();

echo json_encode($comisiones, JSON_UNESCAPED_UNICODE);