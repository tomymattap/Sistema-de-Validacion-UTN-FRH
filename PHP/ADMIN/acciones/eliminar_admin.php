<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

include("../../conexion.php");

$id_admin = $_POST['id'] ?? '';

if (empty($id_admin)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

// Establecer el ID del admin actual para la auditoría
if (isset($_SESSION['user_id'])) {
    $current_admin_id = $_SESSION['user_id'];
    mysqli_query($conexion, "SET @current_admin = '$current_admin_id'");
}

$sql = "DELETE FROM admin WHERE ID_Admin = ?";
$stmt = mysqli_prepare($conexion, $sql);

mysqli_stmt_bind_param($stmt, "s", $id_admin);

if (mysqli_stmt_execute($stmt)) {
    if (mysqli_stmt_affected_rows($stmt) > 0) {
        echo json_encode(['success' => true]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Administrador no encontrado']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el administrador']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>