<?php
session_start();
require '../conexion.php';

header('Content-Type: application/json');

// Verificar si el usuario está logueado y es un alumno
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$email = $data['email'] ?? null;
$telefono = $data['telefono'] ?? null;

if ($email === null || $telefono === null) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit();
}

// Validación del email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
    exit();
}

// Validación del teléfono (solo números y algunos caracteres comunes)
if (!preg_match('/^[0-9\s\-\+\(\)]+$/', $telefono)) {
    echo json_encode(['success' => false, 'message' => 'El número de teléfono contiene caracteres no válidos.']);
    exit();
}

$sql = "UPDATE alumno SET Email_Alumno = ?, Telefono = ? WHERE ID_Cuil_Alumno = ?";
$stmt = $conexion->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error al preparar la consulta.']);
    exit();
}

$stmt->bind_param("ssi", $email, $telefono, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil.']);
}

$stmt->close();
$conexion->close();
?>