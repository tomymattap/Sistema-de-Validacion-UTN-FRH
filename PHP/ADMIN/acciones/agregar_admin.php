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

$legajo = $_POST['legajo'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$rol = $_POST['rol'] ?? 1;

if (empty($legajo) || empty($nombre) || empty($apellido) || empty($email)) {
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

// Establecer el ID del admin actual para la auditoría
if (isset($_SESSION['user_id'])) {
    $current_admin_id = $_SESSION['user_id'];
    mysqli_query($conexion, "SET @current_admin = '$current_admin_id'");
}

// Generar ID_Admin
$id_admin = strtolower($apellido) . '_' . $legajo;

// Hashear la contraseña por defecto (el legajo)
$password_por_defecto = password_hash($legajo, PASSWORD_DEFAULT);
$first_login_done = 0; // Forzar cambio de contraseña

$sql = "INSERT INTO admin (ID_Admin, Legajo, Nombre, Apellido, Email, ID_Rol, Password, first_login_done) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "sisssisi", $id_admin, $legajo, $nombre, $apellido, $email, $rol, $password_por_defecto, $first_login_done);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar el administrador']);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>