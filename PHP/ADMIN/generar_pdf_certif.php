<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . '/../../vendor/setasign/fpdf/fpdf.php');
require_once(__DIR__ . '/../../vendor/setasign/fpdi/src/autoload.php');
require_once(__DIR__ . '/../conexion.php');

use setasign\Fpdi\Fpdi;

// Seguridad
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1 || !isset($_SESSION['alumnos_para_certificar']) || !isset($_SESSION['cert_files_info'])) {
    die("Acceso denegado o no hay datos para generar los certificados.");
}

$alumnos_a_certificar = $_SESSION['alumnos_para_certificar'];
$cert_files_info = $_SESSION['cert_files_info'];
$upload_dir = __DIR__ . '/cert_uploads/';
$output_dir = __DIR__ . '/cert_generated/';

if (!file_exists($output_dir)) {
    mkdir($output_dir, 0777, true);
}

// --- OBTENER DATOS DEL CURSO ---
$first_student_details = reset($alumnos_a_certificar);
$id_curso = $first_student_details['id_curso'];

if (empty($id_curso)) {
    die("Error: No se pudo determinar el curso para la certificación.");
}

$curso_query = $conexion->prepare("SELECT Nombre_Curso, Carga_Horaria FROM CURSO WHERE ID_Curso = ?");
$curso_query->bind_param("i", $id_curso);
$curso_query->execute();
$curso_result = $curso_query->get_result()->fetch_assoc();
$nombre_curso = utf8_decode($curso_result['Nombre_Curso']);
$duracion_curso = utf8_decode($curso_result['Carga_Horaria']);
$curso_query->close();

$generated_files = [];
// --- MANEJO DE ARCHIVOS TEMPORALES ---
// Se definen las rutas de las imágenes subidas. Estas se borrarán después de usarse.
$uploaded_image_paths = [];
$firma_secretario_path = $upload_dir . basename($cert_files_info['firma_secretario_path']);
$firma_docente_director_path = $upload_dir . basename($cert_files_info['firma_docente_director_path']);
$uploaded_image_paths[] = $firma_secretario_path;
$uploaded_image_paths[] = $firma_docente_director_path;

if (!empty($cert_files_info['logo_camara_path'])) {
    $logo_camara_path = $upload_dir . basename($cert_files_info['logo_camara_path']);
    $uploaded_image_paths[] = $logo_camara_path;
} else {
    $logo_camara_path = null;
}

try {
    foreach ($alumnos_a_certificar as $cuil => $details) {
        $estado = $details['estado'];
        $es_director = $cert_files_info['es_director'];
        $nombre_instituto = $cert_files_info['nombre_instituto'];

        $pdf = new Fpdi();
        $pdf->AddPage('L', 'A4');

        $template_path = '';
        if ($es_director && $nombre_instituto && $logo_camara_path && file_exists($logo_camara_path)) {
            $template_path = __DIR__ . '/Modelos_certificados/certificado_externo.pdf';
        } elseif ($estado == 'APROBADO') {
            $template_path = __DIR__ . '/Modelos_certificados/certificado_aprobacion.pdf';
        } elseif ($estado == 'ASISTIDO') {
            $template_path = __DIR__ . '/Modelos_certificados/certificado_asistencia.pdf';
        } else {
            continue;
        }

        if (!file_exists($template_path)) {
            throw new Exception("No se encuentra la plantilla del certificado: " . basename($template_path));
        }

        $pdf->setSourceFile($template_path);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 297, 210);

        $query = $conexion->prepare("SELECT Nombre_Alumno, Apellido_Alumno FROM ALUMNO WHERE ID_Cuil_Alumno = ?");
        $query->bind_param("s", $cuil);
        $query->execute();
        $result = $query->get_result()->fetch_assoc();
        $nombre_completo = utf8_decode($result['Nombre_Alumno'] . ' ' . $result['Apellido_Alumno']);
        $query->close();

        // Obtener el año de la inscripción
        $insc_query = $conexion->prepare("SELECT Anio FROM INSCRIPCION WHERE ID_Cuil_Alumno = ? AND ID_Curso = ?");
        $insc_query->bind_param("si", $cuil, $id_curso);
        $insc_query->execute();
        $insc_result = $insc_query->get_result()->fetch_assoc();
        $anio_cursada = $insc_result ? $insc_result['Anio'] : 'N/A';
        $insc_query->close();


        // --- DATOS ADICIONALES PARA EL PDF ---
        $dni = substr($cuil, 2, -1);
        $fecha_emision = date('d/m/Y');
        $carga_horaria_texto = $duracion_curso . " hs.";


        // --- Escribir datos en el PDF ---
        // Nombre del Alumno
        $pdf->SetFont('Helvetica', 'B', 20);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(15, 60);
        $pdf->Cell(297, 10, $nombre_completo, 0, 1, 'C');
        
        // Nombre del Curso
        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetXY(0, 83);
        $pdf->Cell(297, 10, $nombre_curso, 0, 1, 'C');

        // --- Bloque de DNI, Carga Horaria y Año ---
        $pdf->SetFont('Helvetica', '', 14);
        //$x_pos = 50; // Posición X alineada con la firma del docente/director.
        
        // DNI del Alumno
        $pdf->SetXY(60, 76);
        $pdf->Cell(100, 10, utf8_decode($dni), 0, 1, 'L');

        // Duración del Curso
        $pdf->SetXY(120, 91);
        $pdf->Cell(100, 10, utf8_decode($carga_horaria_texto), 0, 1, 'L');
        
        // Año de Cursada
        $pdf->SetXY(124, 99);
        $pdf->Cell(100, 10, utf8_decode($anio_cursada), 0, 1, 'L');


        // Fecha de Emisión
        $pdf->SetFont('Helvetica', '', 12);
        $pdf->SetXY(238, 113);
        $pdf->Cell(60, 10, utf8_decode($fecha_emision), 0, 1, 'C');

        // Insertar imágenes
        if (file_exists($firma_secretario_path)) {
            $pdf->Image($firma_secretario_path, 221, 130, 50, 0);
        }
        if (file_exists($firma_docente_director_path)) {
            $pdf->Image($firma_docente_director_path, 44, 130, 50, 0);
        }
        if ($logo_camara_path && file_exists($logo_camara_path)) {
            $pdf->Image($logo_camara_path, 210, 15, 40, 0);
        }
        if ($nombre_instituto) {
            $pdf->SetFont('Helvetica', 'I', 12);
            $pdf->SetXY(15, 50);
            $pdf->Cell(0, 10, utf8_decode($nombre_instituto), 0, 1, 'L');
        }

        $output_filename = "certificado_" . str_replace(' ', '_', $result['Nombre_Alumno'] . '_' . $result['Apellido_Alumno']) . "_" . date("Ymd") . ".pdf";
        $pdf->Output($output_dir . $output_filename, 'F');
        $generated_files[] = $output_dir . $output_filename;
    }


//creacion de zip y descarga

    if (count($generated_files) > 0) {
        $zip = new ZipArchive();
        $zip_filename = $output_dir . "certificados_" . date("Y-m-d_H-i-s") . ".zip";

        if ($zip->open($zip_filename, ZipArchive::CREATE) === TRUE) {
            foreach ($generated_files as $file) {
                $zip->addFile($file, basename($file));
            }
            $zip->close();

            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zip_filename) . '"');
            header('Content-Length: ' . filesize($zip_filename));
            header('Pragma: no-cache');
            header('Expires: 0');
            readfile($zip_filename);

            // --- LIMPIEZA DE ARCHIVOS TEMPORALES ---
            foreach ($generated_files as $file) {
                unlink($file); // Borra el PDF individual
            }
            unlink($zip_filename); // Borra el ZIP

            // Borra las imágenes subidas
            foreach ($uploaded_image_paths as $image_path) {
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }

            unset($_SESSION['alumnos_para_certificar']);
            unset($_SESSION['cert_files_info']);
            exit;
        }
    }

} catch (Exception $e) {
    // --- LIMPIEZA EN CASO DE ERROR ---
    // Borra las imágenes subidas para no dejar archivos basura en el servidor.
    foreach ($uploaded_image_paths as $image_path) {
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    die("Error al generar los PDFs: " . $e->getMessage());
}
?>
