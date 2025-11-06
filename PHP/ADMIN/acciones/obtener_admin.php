<?php
session_start();
header('Content-Type: application/json');

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

include("../../conexion.php");

// Validar que se recibió el ID
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

$id_admin = mysqli_real_escape_string($conexion, $_GET['id']);

// Consultar los datos del administrador
$sql = "SELECT ID_Admin, Legajo, Nombre, Apellido, Email, ID_Rol FROM admin WHERE ID_Admin = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $id_admin);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($resultado && $admin = mysqli_fetch_assoc($resultado)) {
    echo json_encode([
        'success' => true,
        'data' => $admin
    ]);
} else {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Administrador no encontrado'
    ]);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>