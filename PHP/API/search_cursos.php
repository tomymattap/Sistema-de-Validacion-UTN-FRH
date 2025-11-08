<?php
header('Content-Type: application/json; charset=utf-8');

include("../conexion.php");

// Parámetro de búsqueda
$search_term = trim($_GET['search'] ?? '');

// Si no hay término de búsqueda, podríamos devolver todos los cursos o un array vacío.
// Por ahora, devolvemos un array vacío si no hay búsqueda.
if (empty($search_term)) {
    // Opcional: podrías devolver todos los cursos si el término está vacío.
    // Para un live search, es mejor devolver vacío hasta que se escriba algo.
    $sql = "SELECT ID_Curso, Nombre_Curso, Categoria, Tipo FROM curso ORDER BY Nombre_Curso ASC";
    $stmt = $conexion->prepare($sql);
} else {
    $sql = "SELECT ID_Curso, Nombre_Curso, Categoria, Tipo 
            FROM curso 
            WHERE Nombre_Curso LIKE ? OR ID_Curso LIKE ?
            ORDER BY Nombre_Curso ASC";
    
    $stmt = $conexion->prepare($sql);
    
    if ($stmt === false) {
        http_response_code(500);
        echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error]);
        exit;
    }
    
    $like_term = "%{$search_term}%";
    $stmt->bind_param('ss', $like_term, $like_term);
}


if ($stmt === false) {
    http_response_code(500);
    echo json_encode(['error' => 'Error en la preparación de la consulta: ' . $conexion->error]);
    exit;
}


$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conexion->close();

echo json_encode($data, JSON_UNESCAPED_UNICODE);
?>
