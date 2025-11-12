<?php
header('Content-Type: application/json');
session_start();
include("../conexion.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_Inscripcion'])) {

    if (isset($_SESSION['user_id'])) {
        $id_admin = $_SESSION['user_id'];
        // ✅ Registrar el admin en MySQL para los triggers (de forma segura)
        $stmt_admin = mysqli_prepare($conexion, "SET @current_admin = ?");
        mysqli_stmt_bind_param($stmt_admin, "s", $id_admin);
        mysqli_stmt_execute($stmt_admin);
    }

    $id = intval($_POST['ID_Inscripcion']);

    // Sentencia preparada para seguridad
    $sql = "DELETE FROM inscripcion WHERE ID_Inscripcion = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Inscripción eliminada correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . mysqli_error($conexion)]);
    }
    exit;
}

// Si la solicitud no es válida
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Solicitud no válida.']);
exit;
?>