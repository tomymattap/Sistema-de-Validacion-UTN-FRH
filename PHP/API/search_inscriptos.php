<?php
header('Content-Type: application/json; charset=utf-8');

include("../conexion.php");

// Parámetros de búsqueda
$q = $_GET['q'] ?? '';
$curso_id = isset($_GET['curso']) ? intval($_GET['curso']) : 0;
$estado = $_GET['estado'] ?? '';
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : 0;
$cuatr = $_GET['cuatr'] ?? '';
$show_all = isset($_GET['all']);

// Si no hay filtros ni búsqueda, y no se pide mostrar todo, devolver vacío
if (!$show_all && empty($q) && $curso_id === 0 && empty($estado) && $anio === 0 && empty($cuatr)) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT i.ID_Inscripcion, a.Nombre_Alumno, a.Apellido_Alumno, a.ID_Cuil_Alumno, c.Nombre_Curso, i.Cuatrimestre, i.Anio, i.Estado_Cursada
        FROM inscripcion i
        JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
        JOIN curso c ON i.ID_Curso = c.ID_Curso
        WHERE 1=1";

$params = [];
$types = '';

if (!empty($q)) {
    $sql .= " AND (a.Nombre_Alumno LIKE ? OR a.Apellido_Alumno LIKE ? OR a.ID_Cuil_Alumno LIKE ? OR c.Nombre_Curso LIKE ?)";
    $like_q = "%{$q}%";
    for ($i = 0; $i < 4; $i++) {
        $params[] = &$like_q;
        $types .= 's';
    }
}

if ($curso_id > 0) {
    $sql .= " AND c.ID_Curso = ?";
    $params[] = &$curso_id;
    $types .= 'i';
}

if (!empty($estado)) {
    $sql .= " AND i.Estado_Cursada = ?";
    $params[] = &$estado;
    $types .= 's';
}

if ($anio > 0) {
    $sql .= " AND i.Anio = ?";
    $params[] = &$anio;
    $types .= 'i';
}

if (!empty($cuatr)) {
    $sql .= " AND i.Cuatrimestre = ?";
    $params[] = &$cuatr;
    $types .= 's';
}

$sql .= " ORDER BY i.Anio DESC, i.ID_Inscripcion DESC";

$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error]);
    exit;
}

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
