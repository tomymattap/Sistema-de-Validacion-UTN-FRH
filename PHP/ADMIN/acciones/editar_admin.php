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
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (empty($id_admin) || empty($nombre) || empty($apellido) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan datos para la actualización']);
    exit;
}

if (!empty($password)) {
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE admin SET Nombre = ?, Apellido = ?, Email = ?, Password = ? WHERE ID_Admin = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "sssss", $nombre, $apellido, $email, $hashed_password, $id_admin);
} else {
    $sql = "UPDATE admin SET Nombre = ?, Apellido = ?, Email = ? WHERE ID_Admin = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $apellido, $email, $id_admin);
}

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el administrador']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>