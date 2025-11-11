<?php
header('Content-Type: application/json');
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cuil = $_POST['ID_Cuil_Alumno'] ?? null;
    $dni = $_POST['DNI_Alumno'] ?? null;
    $nombre = $_POST['Nombre_Alumno'] ?? null;
    $apellido = $_POST['Apellido_Alumno'] ?? null;
    $email = $_POST['Email_Alumno'] ?? null;
    $direccion = $_POST['Direccion'] ?? '';
    $telefono = $_POST['Telefono'] ?? '';

    if (!$cuil || !$dni || !$nombre || !$apellido || !$email) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos marcados con * son obligatorios.']);
        exit;
    }

    // Validar si el CUIL o DNI ya existen
    $stmt = $conexion->prepare("SELECT ID_Cuil_Alumno FROM alumno WHERE ID_Cuil_Alumno = ? OR DNI_Alumno = ?");
    $stmt->bind_param("ss", $cuil, $dni);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El CUIL o DNI ingresado ya pertenece a un alumno registrado.']);
        $stmt->close();
        $conexion->close();
        exit;
    }
    $stmt->close();

    // Insertar nuevo alumno
    $sql = "INSERT INTO alumno (ID_Cuil_Alumno, DNI_Alumno, Nombre_Alumno, Apellido_Alumno, Email_Alumno, Direccion, Telefono) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("sssssss", $cuil, $dni, $nombre, $apellido, $email, $direccion, $telefono);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Estudiante registrado correctamente.', 'cuil' => $cuil]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar al estudiante: ' . $stmt->error]);
    }

    $stmt->close();
    $conexion->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>