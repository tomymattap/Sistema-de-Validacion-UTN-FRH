<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Permitir acceso a Admin (1) y Secretario (3)
if (!isset($_SESSION['user_rol']) || !in_array($_SESSION['user_rol'], [1, 3])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

include("../../conexion.php");

$id_admin = $_POST['id'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$rol = $_POST['rol'] ?? '';

if (empty($id_admin) || empty($nombre) || empty($apellido) || empty($email) || empty($rol)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan datos para la actualización']);
    exit;
}

$sql = "UPDATE admin SET Nombre = ?, Apellido = ?, Email = ?, ID_Rol = ? WHERE ID_Admin = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sssis", $nombre, $apellido, $email, $rol, $id_admin);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el administrador']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>