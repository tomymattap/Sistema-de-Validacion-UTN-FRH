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

$legajo = $_POST['legajo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$rol = $_POST['rol'] ?? 1;

if (empty($legajo) || empty($nombre) || empty($apellido) || empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
    exit;
}

// Validar duplicados
$stmt = mysqli_prepare($conexion, "SELECT ID_Admin FROM admin WHERE Legajo = ? OR Email = ?");
mysqli_stmt_bind_param($stmt, "is", $legajo, $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'El legajo o el email ya se encuentran registrados.']);
    mysqli_stmt_close($stmt);
    exit;
}
mysqli_stmt_close($stmt);

// Generar ID_Admin
$id_admin = strtolower($apellido) . '_' . $legajo;

// Hashear contraseña
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO admin (ID_Admin, Legajo, Nombre, Apellido, Email, Password, ID_Rol) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sissssi", $id_admin, $legajo, $nombre, $apellido, $email, $hashed_password, $rol);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar el administrador']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>