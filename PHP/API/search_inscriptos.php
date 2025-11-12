<?php
header('Content-Type: application/json; charset=utf-8');
include("../conexion.php");

// Parámetros esperados
$search = trim($_GET['search'] ?? '');
$curso = trim($_GET['curso'] ?? '');
$estado = trim($_GET['estado'] ?? '');
$comision = trim($_GET['comision'] ?? '');
$anio = trim($_GET['anio'] ?? '');
$cuatr = trim($_GET['cuatr'] ?? '');
$show_all = isset($_GET['all']) && $_GET['all'] === '1';

// Preparar consulta base
$sql = "SELECT 
            i.ID_Inscripcion,
            a.Nombre_Alumno,
            a.Apellido_Alumno,
            a.ID_Cuil_Alumno,
            c.Nombre_Curso,
            i.Comision,
            i.Cuatrimestre,
            i.Anio,
            i.Estado_Cursada
        FROM inscripcion i
        JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
        JOIN curso c ON i.ID_Curso = c.ID_Curso";

$conditions = [];
$params = [];
$types = '';

// Si no piden "all" y no hay filtros -> devolver vacío (evita consultas masivas)
if (!$show_all && $search === '' && $curso === '' && $estado === '' && $anio === '' && $cuatr === '' && $comision === '') {
    echo json_encode([], JSON_UNESCAPED_UNICODE);
    exit;
}

// Aplicar search (busqueda general)
if ($search !== '') {
    $like = "%{$search}%";
    $conditions[] = "(a.Nombre_Alumno LIKE ? OR a.Apellido_Alumno LIKE ? OR a.ID_Cuil_Alumno LIKE ? OR c.Nombre_Curso LIKE ?)";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

// filtro curso (si viene el nombre o id)
if ($curso !== '') {
    // Si es numérico, asumimos ID, sino filtramos por nombre exacto o LIKE
    if (ctype_digit($curso)) {
        $conditions[] = "c.ID_Curso = ?";
        $params[] = (int)$curso;
        $types .= 'i';
    } else {
        $conditions[] = "c.Nombre_Curso = ?";
        $params[] = $curso;
        $types .= 's';
    }
}

// filtro estado
if ($estado !== '') {
    $conditions[] = "i.Estado_Cursada = ?";
    $params[] = $estado;
    $types .= 's';
}

// filtro comision
if ($comision !== '') {
    $conditions[] = "i.Comision = ?";
    $params[] = $comision;
    $types .= 's';
}

// filtro año
if ($anio !== '') {
    if (ctype_digit($anio)) {
        $conditions[] = "i.Anio = ?";
        $params[] = (int)$anio;
        $types .= 'i';
    }
}

// filtro cuatrimestre
if ($cuatr !== '') {
    $conditions[] = "i.Cuatrimestre = ?";
    $params[] = $cuatr;
    $types .= 's';
}

// Unir condiciones si existen
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(' AND ', $conditions);
}

$sql .= " ORDER BY i.Anio DESC, a.Apellido_Alumno ASC, a.Nombre_Alumno ASC";

// Preparar statement
$stmt = $conexion->prepare($sql);
if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error], JSON_UNESCAPED_UNICODE);
    exit;
}

// Bind de parámetros dinámico
if (!empty($params)) {
    // mysqli_stmt::bind_param requiere variables por referencia
    $bind_names[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

// Ejecutar
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la ejecución: ' . $stmt->error], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conexion->close();
    exit;
}

$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE);
