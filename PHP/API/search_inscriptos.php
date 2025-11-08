<?php
header('Content-Type: application/json; charset=utf-8');

include("../conexion.php");

// --- Recolección de parámetros ---
$q = trim($_GET['q'] ?? '');
$curso_id = filter_input(INPUT_GET, 'curso', FILTER_VALIDATE_INT);
$estado = trim($_GET['estado'] ?? '');
$anio = filter_input(INPUT_GET, 'anio', FILTER_VALIDATE_INT);
$cuatr = trim($_GET['cuatr'] ?? '');
$show_all = isset($_GET['all']);

// --- Construcción de la consulta ---
$sql = "SELECT 
            i.ID_Inscripcion, 
            a.Nombre_Alumno, 
            a.Apellido_Alumno, 
            a.ID_Cuil_Alumno, 
            c.Nombre_Curso, 
            i.Cuatrimestre, 
            i.Anio, 
            i.Estado_Cursada
        FROM inscripcion i
        JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
        JOIN curso c ON i.ID_Curso = c.ID_Curso";

$conditions = [];
$params = [];
$types = '';

// Si no se pide la lista completa y no hay filtros, devolver vacío.
if (!$show_all && empty($q) && !$curso_id && empty($estado) && !$anio && empty($cuatr)) {
    echo json_encode([]);
    exit;
}

// Búsqueda general (live search)
if (!empty($q)) {
    $conditions[] = "(a.Nombre_Alumno LIKE ? OR a.Apellido_Alumno LIKE ? OR a.ID_Cuil_Alumno LIKE ? OR c.Nombre_Curso LIKE ?)";
    $searchTerm = "%{$q}%";
    // Se necesita una variable por cada placeholder en el bind_param
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= 'ssss';
}

// Filtro por ID de curso
if ($curso_id) {
    $conditions[] = "c.ID_Curso = ?";
    $params[] = $curso_id;
    $types .= 'i';
}

// Filtro por estado de cursada
if (!empty($estado)) {
    $conditions[] = "i.Estado_Cursada = ?";
    $params[] = $estado;
    $types .= 's';
}

// Filtro por año
if ($anio) {
    $conditions[] = "i.Anio = ?";
    $params[] = $anio;
    $types .= 'i';
}

// Filtro por cuatrimestre
if (!empty($cuatr)) {
    $conditions[] = "i.Cuatrimestre = ?";
    $params[] = $cuatr;
    $types .= 's';
}

// Unir todas las condiciones
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY i.Anio DESC, a.Apellido_Alumno ASC, a.Nombre_Alumno ASC";

// --- Preparación y ejecución de la consulta ---
$stmt = $conexion->prepare($sql);

if ($stmt === false) {
    http_response_code(500);
    // En un entorno de producción, sería mejor loguear el error que mostrarlo.
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error]);
    exit;
}

// Vincular parámetros si existen
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();

// Devolver los datos en formato JSON
echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>