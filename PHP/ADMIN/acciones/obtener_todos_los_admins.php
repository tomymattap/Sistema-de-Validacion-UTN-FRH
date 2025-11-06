<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

include("../../conexion.php");

$admins = [];
$sql = "SELECT ID_Admin, Legajo, Nombre, Apellido, Email, ID_Rol FROM admin";

$resultado = mysqli_query($conexion, $sql);

if ($resultado) {
    while ($row = mysqli_fetch_assoc($resultado)) {
        $admins[] = $row;
    }
    echo json_encode($admins);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Error al consultar la base de datos']);
}

mysqli_close($conexion);
?>