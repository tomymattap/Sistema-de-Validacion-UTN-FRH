<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
    exit;
}

include("../../conexion.php");

// --- Recibir y limpiar datos del alumno ---
$cuil = $_POST['ID_Cuil_Alumno'] ?? '';
$dni = $_POST['DNI_Alumno'] ?? '';
$nombre = trim($_POST['Nombre_Alumno'] ?? '');
$apellido = trim($_POST['Apellido_Alumno'] ?? '');
$email = trim($_POST['Email_Alumno'] ?? '');
$direccion = trim($_POST['Direccion'] ?? '');
$telefono = trim($_POST['Telefono'] ?? '');

// --- Recibir y limpiar datos de la inscripción ---
$id_curso = $_POST['ID_Curso'] ?? '';
$comision = trim($_POST['Comision'] ?? 'A'); // Por defecto 'A' si no se envía
$cuatrimestre = $_POST['Cuatrimestre'] ?? '';
$anio = $_POST['Anio'] ?? '';

// --- Validaciones del backend ---
if (empty($cuil) || empty($dni) || empty($nombre) || empty($apellido) || empty($email) || empty($id_curso) || empty($cuatrimestre) || empty($anio)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
    exit;
}
if (!preg_match('/^[0-9]{10,11}$/', $cuil)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del CUIL no es válido.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del email no es válido.']);
    exit;
}

// --- Iniciar Transacción ---
mysqli_begin_transaction($conexion);

try {
    // Establecer el ID del admin para la auditoría
    if (isset($_SESSION['user_id'])) {
        $id_admin = $_SESSION['user_id'];
        $stmt_admin = mysqli_prepare($conexion, "SET @current_admin = ?");
        mysqli_stmt_bind_param($stmt_admin, "s", $id_admin);
        mysqli_stmt_execute($stmt_admin);
    }
    // --- LÓGICA PARA DETERMINAR EL ESTADO DE CURSADA AUTOMÁTICAMENTE ---
    $estado_cursada = 'Pendiente'; // Estado por defecto
    $stmt_fechas = mysqli_prepare($conexion, "SELECT Inicio_Curso, Fin_Curso FROM duracion_curso WHERE ID_Curso = ?");
    mysqli_stmt_bind_param($stmt_fechas, "i", $id_curso);
    mysqli_stmt_execute($stmt_fechas);
    $result_fechas = mysqli_stmt_get_result($stmt_fechas);
    $fechas_curso = mysqli_fetch_assoc($result_fechas);
    mysqli_stmt_close($stmt_fechas);

    if ($fechas_curso && !empty($fechas_curso['Inicio_Curso']) && !empty($fechas_curso['Fin_Curso'])) {
        $fecha_actual = new DateTime();
        $fecha_inicio = new DateTime($fechas_curso['Inicio_Curso']);
        $fecha_fin = new DateTime($fechas_curso['Fin_Curso']);

        // Ajustar la hora a 00:00:00 para comparaciones de día completo
        $fecha_actual->setTime(0, 0, 0);

        if ($fecha_actual < $fecha_inicio) {
            $estado_cursada = 'Pendiente';
        } elseif ($fecha_actual >= $fecha_inicio && $fecha_actual <= $fecha_fin) {
            $estado_cursada = 'En Curso';
        } else {
            $estado_cursada = 'Finalizado';
        }
    }

    // --- VALIDACIÓN ADICIONAL: CURSOS EXTERNOS ---
    // 1. Obtener el tipo de curso.
    $stmt_check_curso = mysqli_prepare($conexion, "SELECT Tipo FROM curso WHERE ID_Curso = ?");
    mysqli_stmt_bind_param($stmt_check_curso, "i", $id_curso);
    mysqli_stmt_execute($stmt_check_curso);
    $curso_result = mysqli_stmt_get_result($stmt_check_curso);
    $curso_data = mysqli_fetch_assoc($curso_result);
    mysqli_stmt_close($stmt_check_curso);

    if (!$curso_data) {
        throw new Exception('El curso seleccionado no existe.');
    }

    // 2. Si el curso no es 'GENUINO' (insensible a mayúsculas), verificar que su evaluación esté 'ACEPTADO'.
    if (strtoupper($curso_data['Tipo']) !== 'GENUINO') {
        $stmt_check_eval = mysqli_prepare($conexion, "SELECT Estado_Evaluacion FROM evaluacion_curso_externo WHERE ID_Curso = ?");
        mysqli_stmt_bind_param($stmt_check_eval, "i", $id_curso);
        mysqli_stmt_execute($stmt_check_eval);
        $eval_data = mysqli_stmt_get_result($stmt_check_eval)->fetch_assoc();
        mysqli_stmt_close($stmt_check_eval);

        if (!$eval_data || $eval_data['Estado_Evaluacion'] !== 'ACEPTADO') {
            throw new Exception('No se puede inscribir. El curso externo no tiene una evaluación aprobada.');
        }
    }

    // 1. Verificar si el alumno ya existe por CUIL
    $stmt_check_alumno = mysqli_prepare($conexion, "SELECT ID_Cuil_Alumno FROM alumno WHERE ID_Cuil_Alumno = ?");
    mysqli_stmt_bind_param($stmt_check_alumno, "s", $cuil);
    mysqli_stmt_execute($stmt_check_alumno);
    $alumno_existe = mysqli_stmt_get_result($stmt_check_alumno)->num_rows > 0;
    mysqli_stmt_close($stmt_check_alumno);

    // 2. Si el alumno no existe, insertarlo
    if (!$alumno_existe) {
        $password_generico = password_hash($cuil, PASSWORD_DEFAULT); // Contraseña inicial es el CUIL
        $id_rol_alumno = 2; // Rol de Alumno
        $first_login_done = 0; // Forzar cambio de contraseña

        $sql_insert_alumno = "INSERT INTO alumno (ID_Cuil_Alumno, DNI_Alumno, Nombre_Alumno, Apellido_Alumno, Email_Alumno, Direccion, Telefono, ID_Rol, Password, first_login_done) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_alumno = mysqli_prepare($conexion, $sql_insert_alumno);
        mysqli_stmt_bind_param($stmt_insert_alumno, "ssssssisii", $cuil, $dni, $nombre, $apellido, $email, $direccion, $telefono, $id_rol_alumno, $password_generico, $first_login_done);
        
        if (!mysqli_stmt_execute($stmt_insert_alumno)) {
            throw new Exception('Error al registrar el nuevo alumno: ' . mysqli_stmt_error($stmt_insert_alumno));
        }
        mysqli_stmt_close($stmt_insert_alumno);
    }

    // 3. Verificar si ya existe una inscripción idéntica para ese alumno
    $stmt_check_insc = mysqli_prepare($conexion, "SELECT ID_Inscripcion FROM inscripcion WHERE ID_Cuil_Alumno = ? AND ID_Curso = ? AND Cuatrimestre = ? AND Anio = ?");
    mysqli_stmt_bind_param($stmt_check_insc, "sisi", $cuil, $id_curso, $cuatrimestre, $anio);
    mysqli_stmt_execute($stmt_check_insc);
    $inscripcion_existe = mysqli_stmt_get_result($stmt_check_insc)->num_rows > 0;
    mysqli_stmt_close($stmt_check_insc);

    if ($inscripcion_existe) {
        throw new Exception('El alumno ya está inscripto en este curso para el mismo período.');
    }

    // 4. Insertar la inscripción
    $sql_insert_insc = "INSERT INTO inscripcion (ID_Cuil_Alumno, ID_Curso, Cuatrimestre, Anio, Estado_Cursada, Comision) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt_insert_insc = mysqli_prepare($conexion, $sql_insert_insc);
    mysqli_stmt_bind_param($stmt_insert_insc, "sisiss", $cuil, $id_curso, $cuatrimestre, $anio, $estado_cursada, $comision);

    if (!mysqli_stmt_execute($stmt_insert_insc)) {
        throw new Exception('Error al registrar la inscripción: ' . mysqli_stmt_error($stmt_insert_insc));
    }
    mysqli_stmt_close($stmt_insert_insc);

    // --- Si todo fue bien, confirmar la transacción ---
    mysqli_commit($conexion);
    echo json_encode(['success' => true, 'message' => 'Inscripción y alumno registrados correctamente.']);

} catch (Exception $e) {
    // --- Si algo falló, revertir todo ---
    mysqli_rollback($conexion);
    http_response_code(409); // 409 Conflict es apropiado para duplicados o errores de lógica de negocio
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>