<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../conexion.php');

// Validar que solo los administradores puedan acceder y obtener su ID
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1 || !isset($_SESSION['user_id'])) {
    http_response_code(403);
    die("Acceso denegado. Se requiere rol de administrador.");
}

$id_admin = $_SESSION['user_id'];
$stmt_admin = mysqli_prepare($conexion, "SET @current_admin = ?");
mysqli_stmt_bind_param($stmt_admin, "s", $id_admin);
mysqli_stmt_execute($stmt_admin);
// 1. --- SEGURIDAD Y VALIDACIÓN DE DATOS ---
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    http_response_code(403);
    die("Acceso denegado. Se requiere rol de administrador.");
}

if (empty($_SESSION['alumnos_para_certificar']) || empty($_SESSION['cert_data_for_pdf']) || empty($_SESSION['curso_info'])) {
    http_response_code(400);
    die("Datos insuficientes para generar los certificados. Por favor, inicie el proceso de nuevo.");
}

require_once(__DIR__ . '/../../vendor/autoload.php');

// 2. --- INICIALIZACIÓN Y CONFIGURACIÓN ---
$alumnos_a_certificar = $_SESSION['alumnos_para_certificar'];
$cert_data = $_SESSION['cert_data_for_pdf'];
$curso_info = $_SESSION['curso_info'];

$output_dir_for_zip = __DIR__ . '/cert_generated/'; // El ZIP se creará aquí temporalmente
$modelos_dir = __DIR__ . '/Modelos_certificados/';

// Crear directorio de salida si no existe (para el ZIP)
if (!is_dir($output_dir_for_zip)) {
    mkdir($output_dir_for_zip, 0777, true);
}

$zip_file_path = ''; // Se llenará con la ruta del ZIP generado
$temp_files_to_delete = array_values($cert_data['files']); // Archivos subidos (firmas, logos)

// Registrar función de limpieza para que se ejecute al final
register_shutdown_function(function() use (&$zip_file_path, &$temp_files_to_delete) {
    // Borra el ZIP generado
    if (!empty($zip_file_path) && file_exists($zip_file_path)) {
        unlink($zip_file_path);
    }
    // Borra los archivos temporales subidos (firmas, logos)
    foreach ($temp_files_to_delete as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }
});

try {
    // 3. --- OBTENER DATOS COMUNES DEL CURSO ---
    $curso_query = $conexion->prepare("SELECT Nombre_Curso, Carga_Horaria, Tipo FROM CURSO WHERE ID_Curso = ?");
    $curso_query->bind_param("i", $curso_info['id_curso']);
    $curso_query->execute();
    $curso_result = $curso_query->get_result()->fetch_assoc();
    if (!$curso_result) {
        throw new Exception("No se encontraron datos para el curso ID: " . htmlspecialchars($curso_info['id_curso']));
    }
    $nombre_curso = $curso_result['Nombre_Curso'];
    $duracion_curso = $curso_result['Carga_Horaria'];
    $curso_query->close();

    $pdf_data_for_zip = []; // Almacenará [nombre_archivo => contenido_pdf]

    // 4. --- GENERACIÓN DE PDFS INDIVIDUALES ---
    foreach ($alumnos_a_certificar as $cuil => $details) {
        $estado_aprobacion = $details['estado']; // APROBADO o ASISTIDO

        // --- Selección de plantilla ---
        $template_path = '';
        if ($cert_data['tipo_certificado'] === 'genuino') {
            $template_path = ($estado_aprobacion === 'APROBADO') 
                ? $modelos_dir . 'certificado_aprobacion.pdf' 
                : $modelos_dir . 'certificado_asistencia.pdf';
        } elseif ($cert_data['tipo_certificado'] === 'externo') {
            $template_path = $modelos_dir . 'certificado_externo.pdf';
        }

        if (!file_exists($template_path)) {
            throw new Exception("No se encuentra la plantilla del certificado: " . basename($template_path));
        }

        // --- Obtener datos del alumno ---
        $alumno_query = $conexion->prepare("SELECT Nombre_Alumno, Apellido_Alumno FROM ALUMNO WHERE ID_Cuil_Alumno = ?");
        $alumno_query->bind_param("s", $cuil);
        $alumno_query->execute();
        $alumno_result = $alumno_query->get_result()->fetch_assoc();
        if (!$alumno_result) continue; // Saltar si no se encuentra el alumno
        $nombre_completo = mb_strtoupper($alumno_result['Nombre_Alumno'] . ' ' . $alumno_result['Apellido_Alumno']);
        $alumno_query->close();

        // Obtener ID_Inscripcion para el alumno y contexto del curso
        $inscripcion_query = $conexion->prepare("SELECT ID_Inscripcion FROM INSCRIPCION WHERE ID_Cuil_Alumno = ? AND ID_Curso = ? AND Anio = ? AND Cuatrimestre = ?");
        $inscripcion_query->bind_param("siss", $cuil, $curso_info['id_curso'], $curso_info['anio'], $curso_info['cuatrimestre']);
        $inscripcion_query->execute();
        $inscripcion_result = $inscripcion_query->get_result()->fetch_assoc();
        if (!$inscripcion_result) {
            error_log("No se encontró ID_Inscripcion para CUIL: $cuil, Curso: {$curso_info['id_curso']}");
            continue; // Saltar si no se encuentra la inscripción
        }
        $id_inscripcion_certif = $inscripcion_result['ID_Inscripcion'];
        $inscripcion_query->close();

        // Obtener CUV desde la tabla CERTIFICACION
        $cuv_query = $conexion->prepare("SELECT ID_CUV FROM CERTIFICACION WHERE ID_Inscripcion_Certif = ?");
        $cuv_query->bind_param("i", $id_inscripcion_certif);
        $cuv_query->execute();
        $cuv_result = $cuv_query->get_result()->fetch_assoc();
        $cuv = $cuv_result ? $cuv_result['ID_CUV'] : 'CUV_NO_ENCONTRADO';
        $cuv_query->close();


        // Obtener Fecha_Emision de la tabla CERTIFICACION
        $certificacion_query = $conexion->prepare("SELECT Fecha_Emision FROM CERTIFICACION WHERE ID_Inscripcion_Certif = ?");
        $certificacion_query->bind_param("i", $id_inscripcion_certif);
        $certificacion_query->execute();
        $certificacion_result = $certificacion_query->get_result()->fetch_assoc();
        if (!$certificacion_result) {
            error_log("No se encontró certificación para ID_Inscripcion: $id_inscripcion_certif");
            continue; // Saltar si no se encuentra la certificación
        }
        // Formatear la fecha a d/m/Y
        $fecha_emision_dt = new DateTime($certificacion_result['Fecha_Emision']);
        $fecha_emision = $fecha_emision_dt->format('d/m/Y');
        $certificacion_query->close();

        // Extraer DNI del CUIL (sin los dos primeros y el último número)
        $dni_alumno = substr($cuil, 2, -1);

        // --- Definir rutas de imágenes desde los datos del certificado ---
        $logo_path = $cert_data['files']['logo_institucional'] ?? null;
        $firma_secretario = $cert_data['files']['firma_secretario'] ?? null;
        $firma_docente = $cert_data['files']['firma_docente'] ?? null;
        $firma_director = $cert_data['files']['firma_director'] ?? null;

        // --- Inicializar mPDF ---
        $mpdf = new \Mpdf\Mpdf(['mode' => 'utf-8', 'format' => 'A4-L']);
        $mpdf->SetDisplayMode('fullpage');
        $mpdf->setSourceFile($template_path);
        $tplId = $mpdf->importPage(1);
        $mpdf->AddPage('L');
        $mpdf->useTemplate($tplId);

        // --- Construir el texto principal del certificado ---
        $tipo_actividad = !empty($cert_data['tipo_actividad']) ? htmlspecialchars($cert_data['tipo_actividad']) : 'el curso'; // Fallback por si no se proveyó

        $main_certificate_text = '';
        if ($cert_data['tipo_certificado'] === 'genuino') {
            $main_certificate_text = sprintf(
                'DNI <b><i>%s</i></b> ha satisfecho las condiciones exigidas por %s "<b><i>%s</i></b>" con una carga horaria de <b><i>%s</i></b>hs, dictado en la UTN Facultad Regional Haedo, durante el año <b><i>%s</i></b>, se le otorga el presente certificado.',
                htmlspecialchars($dni_alumno),
                $tipo_actividad,
                htmlspecialchars($nombre_curso),
                htmlspecialchars($duracion_curso),
                htmlspecialchars($curso_info['anio'])
            );
        } elseif ($cert_data['tipo_certificado'] === 'externo') {
            $dictado_por_text = !empty($cert_data['institutos_codictantes']) ? ' dictado por ' . htmlspecialchars($cert_data['institutos_codictantes']) : '';
            $desarrollado_por_text = !empty($cert_data['camara_organizadora']) ? ' y desarrollado por ' . htmlspecialchars($cert_data['camara_organizadora']) : '';
            
            $main_certificate_text = sprintf(
                'DNI <b><i>%s</i></b> ha satisfecho las condiciones exigidas por %s "<b><i>%s</i></b>" con una carga horaria de <b><i>%s</i></b>hs,%s%s, durante el año <b><i>%s</i></b>, se le otorga el presente certificado.',
                htmlspecialchars($dni_alumno),
                $tipo_actividad,
                htmlspecialchars($nombre_curso),
                htmlspecialchars($duracion_curso),
                $dictado_por_text,
                $desarrollado_por_text,
                htmlspecialchars($curso_info['anio'])
            );
        }

        // --- INICIO: Generar QR ---
        // URL para el QR. Ajusta la URL base según sea necesario.
        $validation_url = "http://localhost/Sistema_Validacion/index.html?cuv=" . urlencode($cuv);
        $qrCodeImage = (new \chillerlan\QRCode\QRCode)->render($validation_url);


        // --- Construir HTML dinámico con CSS para posicionamiento de texto ---
        $css = '
        <style>
            /* Nombre del alumno */
            .nombre-alumno {
                position: absolute;
                left: 25mm; right: 0;
                text-align: center;
                font-family: times new roman, serif;
                font-size: 22pt;
                font-weight: bold;
                font-style: italic;
            }

            /* Texto principal del certificado */
            .main-certificate-text {
                position: absolute;
                left: 37.8mm;
                width: 246.6mm;
                height: auto;
                font-size: 17pt;
                text-align: justify;
                line-height: 1.2;
                letter-spacing: 0.081em;
            }

            /* Fecha inferior derecha */
            .fecha-emision {
                position: absolute;
                left: 240mm;
                font-size: 14pt;
            }
            
            .codigo-cuv {
                position: absolute;
                top: 169mm; /* ajustá esta coordenada según la posición real */
                left: 137mm; /* centrado respecto al QR */
                font-size: 11pt;
                font-family: dejavusanscondensed, sans-serif;
                font-weight: bold;
                text-align: center;
            }
        </style>';

        // --- Ajustes según el tipo de certificado ---
        if ($cert_data['tipo_certificado'] === 'genuino') {
            $css .= '
            <style>
                .nombre-alumno { top: 62.3mm; }
                .main-certificate-text { top: 80mm; }
                .fecha-emision { top: 110mm; }
            </style>';
        } elseif ($cert_data['tipo_certificado'] === 'externo') {
            $css .= '
            <style>
                .nombre-alumno { top: 55.6mm; }
                .main-certificate-text { top: 76mm; }
                .fecha-emision { top: 118mm; }
            </style>';
        }
        
        $html_content = $css . '
        <div class="text-container nombre-alumno">' . htmlspecialchars($nombre_completo) . '</div>
        <div class="text-container main-certificate-text">' . $main_certificate_text . '</div>
        <div class="text-container fecha-emision"> Haedo, ' . htmlspecialchars($fecha_emision) . '</div>
        <div class="codigo-cuv">Código: ' . htmlspecialchars($cuv) . '</div>';


        // Escribir el contenido HTML (texto y CUV) en el PDF
        $mpdf->WriteHTML($html_content);

        // --- Añadir imágenes y qr con posicionamiento absoluto ---
        if ($logo_path && file_exists($logo_path)) {
            $mpdf->Image($logo_path, 33, 5, 250, 0, 'PNG');
        }
        if ($firma_secretario && file_exists($firma_secretario)) {
            $mpdf->Image($firma_secretario, 225, 135, 50, 0, 'PNG');
        }
        $mpdf->Image($qrCodeImage, 140, 130, 40, 40, 'png');

        if ($cert_data['tipo_certificado'] === 'genuino') {
            if ($firma_docente && file_exists($firma_docente)) {
                $mpdf->Image($firma_docente, 45, 135, 45, 0, 'PNG');
            }
        } elseif ($cert_data['tipo_certificado'] === 'externo') {
            // Para externos, solo se sube una firma (docente o director)
            if ($firma_docente && file_exists($firma_docente)) {
                $mpdf->Image($firma_docente, 45, 135, 45, 0, 'PNG');
            } elseif ($firma_director && file_exists($firma_director)) {
                $mpdf->Image($firma_director, 45, 135, 45, 0, 'PNG');
            }
        }

        // --- Generar contenido del PDF y guardarlo en la BD ---
        $safe_filename = "certificado_" . preg_replace('/[^a-z0-9_]/i', '_', $nombre_completo) . ".pdf";
        $pdf_content = $mpdf->Output(null, \Mpdf\Output\Destination::STRING_RETURN);
        
        // Guardar para el ZIP
        $pdf_data_for_zip[$safe_filename] = $pdf_content;

        // --- INICIO: Guardar PDF en la BD ---
        $update_query = $conexion->prepare("UPDATE CERTIFICACION SET Archivo = ? WHERE ID_Inscripcion_Certif = ?");
        if ($update_query) {
            $null = NULL; // Requerido para bind_param con tipo 'b'
            $update_query->bind_param("bi", $null, $id_inscripcion_certif);
            $update_query->send_long_data(0, $pdf_content); // Enviar contenido del PDF como LONGBLOB
            if (!$update_query->execute()) {
                error_log("Fallo al actualizar el BLOB del certificado para ID de inscripción: " . $id_inscripcion_certif . " - " . $update_query->error);
            }
            $update_query->close();
        } else {
            error_log("Fallo al preparar la consulta de actualización del certificado: " . $conexion->error);
        }
        // --- FIN: Guardar PDF en la BD ---
    }

    // 5. --- CREACIÓN Y DESCARGA DEL ZIP ---
    if (!empty($pdf_data_for_zip)) {
        $zip = new ZipArchive();
        $zip_filename = "certificados_" . date("Ymd-His") . ".zip";
        $zip_file_path = $output_dir_for_zip . $zip_filename;
        
        if ($zip->open($zip_file_path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($pdf_data_for_zip as $filename => $content) {
                $zip->addFromString($filename, $content);
            }
            $zip->close();
            
            // Limpiar sesión
            unset($_SESSION['alumnos_para_certificar'], $_SESSION['cert_data_for_pdf'], $_SESSION['curso_info']);

            // Forzar descarga
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zip_file_path) . '"');
            header('Content-Length: ' . filesize($zip_file_path));
            header('Pragma: no-cache');
            header('Expires: 0');
            
            ob_clean();
            flush();
            
            readfile($zip_file_path);
            exit; // Terminar script después de la descarga
        } else {
            throw new Exception("No se pudo crear el archivo ZIP.");
        }
    } else {
        throw new Exception("No se generó ningún certificado.");
    }

} catch (Exception $e) {
    http_response_code(500);
    // Limpiar sesión en caso de error
    unset($_SESSION['alumnos_para_certificar'], $_SESSION['cert_data_for_pdf'], $_SESSION['curso_info']);
    die("Error al generar los PDFs: " . $e->getMessage());
}
?>