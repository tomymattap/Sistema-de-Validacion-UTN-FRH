<?php
header('Content-Type: application/json');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificación de seguridad y permisos
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado. Se requiere rol de administrador.']);
    exit;
}

include("../conexion.php");

// 2. Manejo del archivo subido
if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Error en la subida del archivo o archivo no proporcionado.']);
    exit;
}

$tmpName = $_FILES['archivo']['tmp_name'];
$fileType = mime_content_type($tmpName);

// Validar que sea un archivo CSV
if ($fileType !== 'text/plain' && $fileType !== 'text/csv') {
    http_response_code(415);
    echo json_encode(['error' => 'Formato de archivo no válido. Solo se permiten archivos CSV.']);
    exit;
}

$inserted_count = 0;
$errors = [];
$row_number = 1;

// --- Iniciar Transacción ---
mysqli_begin_transaction($conexion);

try {
    $file = fopen($tmpName, 'r');
    if (!$file) {
        throw new Exception("No se pudo abrir el archivo subido.");
    }

    // 3. Leer la cabecera para mapear columnas
    $header = fgetcsv($file);
    if (!$header) {
        throw new Exception("El archivo CSV está vacío o la cabecera no es legible.");
    }

    // --- Normalización de cabeceras ---

    // Eliminar BOM (Byte Order Mark) del primer elemento si existe
    if (isset($header[0])) {
        $header[0] = preg_replace('/^\x{FEFF}/u', '', $header[0]);
    }

    // Función para normalizar los encabezados (quitar acentos, espacios, minúsculas)
    function normalize_header_name($str) {
        $str = trim($str);
        $str = strtolower($str);
        $unwanted_array = ['á'=>'a', 'é'=>'e', 'í'=>'i', 'ó'=>'o', 'ú'=>'u', 'ñ'=>'n'];
        return strtr($str, $unwanted_array);
    }

    $normalized_header = array_map('normalize_header_name', $header);

    // Cabeceras esperadas (normalizadas). Se cambia 'id_curso' por 'curso'.
    $expected_headers = ['cuil', 'dni', 'nombre', 'apellido', 'email', 'direccion', 'telefono', 'curso', 'comision', 'cuatrimestre', 'anio'];
    
    $col_indices = [];
    $missing_headers = [];
    foreach ($expected_headers as $expected) {
        $index = array_search($expected, $normalized_header);
        if ($index === false) {
            $missing_headers[] = $expected;
        } else {
            $col_indices[$expected] = $index;
        }
    }

    // Si faltan cabeceras, lanzar excepción que será capturada y mostrada al usuario.
    if (!empty($missing_headers)) {
        throw new Exception("El archivo CSV no tiene las columnas requeridas. Faltan: " . implode(', ', $missing_headers));
    }

    // 4. Procesar cada fila del CSV
    while (($row = fgetcsv($file)) !== false) {
        $row_number++;

        // Extraer datos usando los índices de columna
        $cuil = trim($row[$col_indices['cuil']]);
        $dni = trim($row[$col_indices['dni']]);
        $nombre = trim($row[$col_indices['nombre']]);
        $apellido = trim($row[$col_indices['apellido']]);
        $email = trim($row[$col_indices['email']]);
        $direccion = trim($row[$col_indices['direccion']]);
        $telefono = trim($row[$col_indices['telefono']]);
        $id_curso = trim($row[$col_indices['curso']]); // Se usa 'curso' como key
        $comision = trim($row[$col_indices['comision']]);
        $cuatrimestre = trim($row[$col_indices['cuatrimestre']]);
        $anio = trim($row[$col_indices['anio']]);

        // --- Validaciones básicas ---
        if (empty($cuil) || empty($dni) || empty($nombre) || empty($apellido) || empty($email) || empty($id_curso)) {
            $errors[] = "Fila {$row_number}: Faltan datos obligatorios (CUIL, DNI, Nombre, Apellido, Email, ID_Curso).";
            continue;
        }
        if (!preg_match('/^[0-9]{10,11}$/', $cuil)) {
            $errors[] = "Fila {$row_number}: CUIL '{$cuil}' no es válido.";
            continue;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Fila {$row_number}: Email '{$email}' no es válido.";
            continue;
        }

        // 5. Lógica de inserción (similar a registrar_inscripcion_completa.php)

        // 5.1. Insertar o encontrar alumno
        $stmt_check_alumno = mysqli_prepare($conexion, "SELECT ID_Cuil_Alumno FROM alumno WHERE ID_Cuil_Alumno = ?");
        mysqli_stmt_bind_param($stmt_check_alumno, "s", $cuil);
        mysqli_stmt_execute($stmt_check_alumno);
        mysqli_stmt_store_result($stmt_check_alumno);
        $alumno_existe = mysqli_stmt_num_rows($stmt_check_alumno) > 0;
        mysqli_stmt_close($stmt_check_alumno);

        if (!$alumno_existe) {
            $password_generico = password_hash($cuil, PASSWORD_DEFAULT);
            $id_rol_alumno = 2;
            $first_login_done = 0;

            $sql_insert_alumno = "INSERT INTO alumno (ID_Cuil_Alumno, DNI_Alumno, Nombre_Alumno, Apellido_Alumno, Email_Alumno, Direccion, Telefono, ID_Rol, Password, first_login_done) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_insert_alumno = mysqli_prepare($conexion, $sql_insert_alumno);
            mysqli_stmt_bind_param($stmt_insert_alumno, "sssssssisi", $cuil, $dni, $nombre, $apellido, $email, $direccion, $telefono, $id_rol_alumno, $password_generico, $first_login_done);
            
            if (!mysqli_stmt_execute($stmt_insert_alumno)) {
                $errors[] = "Fila {$row_number}: Error al crear el alumno con CUIL {$cuil}.";
                continue;
            }
            mysqli_stmt_close($stmt_insert_alumno);
        }

        // 5.2. Insertar inscripción (si no existe)
        $stmt_check_insc = mysqli_prepare($conexion, "SELECT ID_Inscripcion FROM inscripcion WHERE ID_Cuil_Alumno = ? AND ID_Curso = ? AND Cuatrimestre = ? AND Anio = ?");
        mysqli_stmt_bind_param($stmt_check_insc, "sisi", $cuil, $id_curso, $cuatrimestre, $anio);
        mysqli_stmt_execute($stmt_check_insc);
        mysqli_stmt_store_result($stmt_check_insc);
        $inscripcion_existe = mysqli_stmt_num_rows($stmt_check_insc) > 0;
        mysqli_stmt_close($stmt_check_insc);

        if ($inscripcion_existe) {
            $errors[] = "Fila {$row_number}: El alumno con CUIL {$cuil} ya está inscripto en el curso {$id_curso} para ese período.";
            continue;
        }

        $estado_cursada = 'Pendiente'; // Estado por defecto para carga masiva

        $sql_insert_insc = "INSERT INTO inscripcion (ID_Cuil_Alumno, ID_Curso, Cuatrimestre, Anio, Estado_Cursada, Comision) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert_insc = mysqli_prepare($conexion, $sql_insert_insc);
        mysqli_stmt_bind_param($stmt_insert_insc, "sisiss", $cuil, $id_curso, $cuatrimestre, $anio, $estado_cursada, $comision);

        if (mysqli_stmt_execute($stmt_insert_insc)) {
            $inserted_count++;
        } else {
            $errors[] = "Fila {$row_number}: Error al registrar la inscripción para el CUIL {$cuil}.";
        }
        mysqli_stmt_close($stmt_insert_insc);
    }
    fclose($file);

    // 6. Finalizar la transacción
    if (empty($errors)) {
        mysqli_commit($conexion);
        $mensaje = "Carga masiva completada. Se procesaron e insertaron {$inserted_count} inscripciones nuevas.";
        echo json_encode(['mensaje' => $mensaje, 'inserted_count' => $inserted_count]);
    } else {
        mysqli_rollback($conexion);
        $error_summary = "La carga masiva falló y fue revertida. Se encontraron " . count($errors) . " errores. Detalles: " . implode(" | ", array_slice($errors, 0, 5));
        http_response_code(400);
        echo json_encode(['error' => $error_summary, 'details' => $errors]);
    }

} catch (Throwable $e) {
    mysqli_rollback($conexion);
    http_response_code(500);
    echo json_encode(['error' => 'Error crítico durante el proceso: ' . $e->getMessage()]);
}

mysqli_close($conexion);
?>