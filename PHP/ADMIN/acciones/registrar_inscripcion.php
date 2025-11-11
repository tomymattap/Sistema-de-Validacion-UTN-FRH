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

// Recibir datos del formulario
$cuil = $_POST['cuil'] ?? '';
$dni = $_POST['dni'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$apellido = $_POST['apellido'] ?? '';
$email = $_POST['email'] ?? '';
$direccion = $_POST['direccion'] ?? '';
$telefono = $_POST['telefono'] ?? '';
$curso = $_POST['curso'] ?? '';
$cuatrimestre = $_POST['cuatrimestre'] ?? '';
$anio = $_POST['anio'] ?? '';
$estado_cursada = $_POST['estado_cursada'] ?? '';

// Validar campos obligatorios
if (empty($cuil) || empty($dni) || empty($nombre) || empty($apellido) || empty($email) || empty($curso) || empty($cuatrimestre) || empty($anio) || empty($estado_cursada)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados.']);
    exit;
}

// Validar formato de CUIL
if (!preg_match('/^[0-9]{11}$/', $cuil)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El CUIL debe tener 11 dígitos numéricos.']);
    exit;
}

// Validar formato de DNI
if (!preg_match('/^[0-9]{7,8}$/', $dni)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El DNI debe tener entre 7 y 8 dígitos numéricos.']);
    exit;
}

// Validar formato de Email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido.']);
    exit;
}

// Verificar si el alumno ya existe por CUIL
$stmt_check_alumno = mysqli_prepare($conexion, "SELECT ID_Cuil_Alumno FROM alumno WHERE ID_Cuil_Alumno = ?");
mysqli_stmt_bind_param($stmt_check_alumno, "s", $cuil);
mysqli_stmt_execute($stmt_check_alumno);
mysqli_stmt_store_result($stmt_check_alumno);

$alumno_existe = mysqli_stmt_num_rows($stmt_check_alumno) > 0;
mysqli_stmt_close($stmt_check_alumno);

// Si el alumno no existe, insertarlo
if (!$alumno_existe) {
    $password_generico = password_hash("123456", PASSWORD_DEFAULT); // Contraseña genérica
    $id_rol_alumno = 2; // Rol de Alumno

    $sql_insert_alumno = "INSERT INTO alumno (ID_Cuil_Alumno, DNI_Alumno, Nombre_Alumno, Apellido_Alumno, Email_Alumno, Dirección, Teléfono, ID_Rol, Password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert_alumno = mysqli_prepare($conexion, $sql_insert_alumno);
    mysqli_stmt_bind_param($stmt_insert_alumno, "sisssssis", $cuil, $dni, $nombre, $apellido, $email, $direccion, $telefono, $id_rol_alumno, $password_generico);

    if (!mysqli_stmt_execute($stmt_insert_alumno)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al registrar el alumno: ' . mysqli_error($conexion)]);
        mysqli_stmt_close($stmt_insert_alumno);
        mysqli_close($conexion);
        exit;
    }
    mysqli_stmt_close($stmt_insert_alumno);
} else {
    // Si el alumno existe, verificar si ya está inscripto en el mismo curso y cuatrimestre/año
    $stmt_check_inscripcion = mysqli_prepare($conexion, "SELECT ID_Inscripcion FROM inscripcion WHERE ID_Cuil_Alumno = ? AND ID_Curso = ? AND Cuatrimestre = ? AND Anio = ?");
    mysqli_stmt_bind_param($stmt_check_inscripcion, "sisi", $cuil, $curso, $cuatrimestre, $anio);
    mysqli_stmt_execute($stmt_check_inscripcion);
    mysqli_stmt_store_result($stmt_check_inscripcion);

    if (mysqli_stmt_num_rows($stmt_check_inscripcion) > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El alumno ya está inscripto en este curso para el mismo período.']);
        mysqli_stmt_close($stmt_check_inscripcion);
        mysqli_close($conexion);
        exit;
    }
    mysqli_stmt_close($stmt_check_inscripcion);
}

// Insertar en la tabla de inscripción
$sql_insert_inscripcion = "INSERT INTO inscripcion (ID_Cuil_Alumno, ID_Curso, Cuatrimestre, Anio, Estado_Cursada) VALUES (?, ?, ?, ?, ?)";
$stmt_insert_inscripcion = mysqli_prepare($conexion, $sql_insert_inscripcion);
mysqli_stmt_bind_param($stmt_insert_inscripcion, "sisss", $cuil, $curso, $cuatrimestre, $anio, $estado_cursada);

if (mysqli_stmt_execute($stmt_insert_inscripcion)) {
    echo json_encode(['success' => true, 'message' => 'Inscripción registrada correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar la inscripción: ' . mysqli_error($conexion)]);
}

mysqli_stmt_close($stmt_insert_inscripcion);
mysqli_close($conexion);
?>