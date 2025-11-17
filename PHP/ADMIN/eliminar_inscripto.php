<?php
header('Content-Type: application/json');
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Establecer el ID del admin actual para la auditoría
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['user_id'])) {
        $current_admin_id = $_SESSION['user_id'];
        $conexion->query("SET @current_admin = '$current_admin_id'");
    }

    $id = intval($_POST['ID_Inscripcion'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID de inscripción no válido.']);
        exit;
    }

    // 1. Obtener el CUIL del alumno antes de eliminar la inscripción
    $queryAlumno = $conexion->prepare("SELECT ID_Cuil_Alumno FROM inscripcion WHERE ID_Inscripcion = ?");
    $queryAlumno->bind_param("i", $id);
    $queryAlumno->execute();
    $queryAlumno->store_result();

    if ($queryAlumno->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'La inscripción no existe.']);
        $queryAlumno->close();
        $conexion->close();
        exit;
    }

    $queryAlumno->bind_result($cuilAlumno);
    $queryAlumno->fetch();
    $queryAlumno->close();

    // 2. Eliminar la inscripción
    $stmt = $conexion->prepare("DELETE FROM inscripcion WHERE ID_Inscripcion = ?");
    $stmt->bind_param("i", $id);

    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar inscripción: ' . $stmt->error]);
        $stmt->close();
        $conexion->close();
        exit;
    }
    $stmt->close();

    // 3. Verificar si el alumno aún tiene otras inscripciones
    $checkAlumno = $conexion->prepare("SELECT COUNT(*) FROM inscripcion WHERE ID_Cuil_Alumno = ?");
    $checkAlumno->bind_param("s", $cuilAlumno);
    $checkAlumno->execute();
    $checkAlumno->bind_result($cantidad);
    $checkAlumno->fetch();
    $checkAlumno->close();

    // 4. Si no tiene más inscripciones, eliminar también el alumno
    if ($cantidad == 0) {
        $deleteAlumno = $conexion->prepare("DELETE FROM alumno WHERE ID_Cuil_Alumno = ?");
        $deleteAlumno->bind_param("s", $cuilAlumno);
        if ($deleteAlumno->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Inscripción y alumno eliminados correctamente.'
            ]);
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Inscripción eliminada, pero no se pudo eliminar el alumno: ' . $deleteAlumno->error
            ]);
        }
        $deleteAlumno->close();
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Inscripción eliminada correctamente (el alumno tiene otras inscripciones).'
        ]);
    }

    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>