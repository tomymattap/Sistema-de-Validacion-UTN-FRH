<?php
header('Content-Type: application/json');
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuil = $_POST['ID_Cuil_Alumno'] ?? null;
    $id_curso = $_POST['ID_Curso'] ?? null;
    $comision = $_POST['Comision'] ?? null;
    $cuatrimestre = $_POST['Cuatrimestre'] ?? null;
    $anio = $_POST['Anio'] ?? null;
    $estado = $_POST['Estado_Cursada'] ?? 'Pendiente';

    if (!$cuil || !$id_curso || !$comision || !$cuatrimestre || !$anio) {
        echo json_encode(['success' => false, 'message' => 'Faltan datos para completar la inscripción.']);
        exit;
    }

    // Validar que el alumno exista
    $stmt = $conexion->prepare("SELECT ID_Cuil_Alumno FROM alumno WHERE ID_Cuil_Alumno = ?");
    $stmt->bind_param("s", $cuil);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'El alumno especificado no existe.']);
        $stmt->close();
        $conexion->close();
        exit;
    }
    $stmt->close();

    // Insertar la inscripción
    $sql = "INSERT INTO inscripcion (ID_Cuil_Alumno, ID_Curso, Comision, Cuatrimestre, Anio, Estado_Cursada) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sissss", $cuil, $id_curso, $comision, $cuatrimestre, $anio, $estado);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => '¡Inscripción realizada con éxito!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar la inscripción: ' . $stmt->error]);
    }

    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>