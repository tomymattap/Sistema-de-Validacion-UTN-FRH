<?php
session_start();
require("../../vendor/autoload.php");
include("../conexion.php");

use setasign\Fpdi\Fpdi;

if (!isset($_SESSION['alumnos_para_certificar']) || !isset($_SESSION['curso_info'])) {
    // Redirigir si no hay datos, en lugar de mostrar un error fatal.
    header("Location: seleccionar_alum_certif.php");
    exit();
}

$alumnos_cuil = $_SESSION['alumnos_para_certificar'];
$curso_info = $_SESSION['curso_info'];
$id_curso = $curso_info['id_curso'];
$anio_cursada = $curso_info['anio'];

// --- Obtener datos del curso ---
$curso_query = $conexion->prepare("SELECT Nombre_Curso, Carga_Horaria FROM CURSO WHERE ID_Curso = ?");
$curso_query->bind_param("i", $id_curso);
$curso_query->execute();
$curso_result = $curso_query->get_result()->fetch_assoc();
$nombre_curso = $curso_result['Nombre_Curso'];
$carga_horaria = $curso_result['Carga_Horaria'];

$pdf_files = [];
$temp_dir = __DIR__ . '/certificados_temp/';
if (!is_dir($temp_dir)) {
    mkdir($temp_dir, 0777, true);
}

foreach ($alumnos_cuil as $cuil) {
    // --- Obtener datos del alumno ---
    $alumno_query = $conexion->prepare("SELECT Nombre_Alumno, Apellido_Alumno FROM ALUMNO WHERE ID_Cuil_Alumno = ?");
    $alumno_query->bind_param("s", $cuil);
    $alumno_query->execute();
    $alumno_result = $alumno_query->get_result()->fetch_assoc();
    $nombre_completo = $alumno_result['Nombre_Alumno'] . ' ' . $alumno_result['Apellido_Alumno'];
    $dni = substr($cuil, 2, -1);

    // --- Generar PDF usando la plantilla ---
    $pdf = new Fpdi();

    // Importar la primera página de la plantilla
    $pdf->setSourceFile("Modelos_certificados/certificado_aprobacion.pdf");
    $tplIdx = $pdf->importPage(1);
    $size = $pdf->getTemplateSize($tplIdx);
   
    // Crea una página con el mismo tamaño/orientación del template
    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
    $pdf->useTemplate($tplIdx); // A4 Landscape

    // --- Escribir los datos dinámicos (las coordenadas X, Y son aproximadas) ---

    // Nombre del Alumno
    $pdf->SetFont('Helvetica', 'B', 20);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(0, 85);
    $pdf->Cell(297, 10, utf8_decode(strtoupper($nombre_completo)), 0, 1, 'C');

    // Nombre del Curso
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->SetXY(0, 120);
    $pdf->Cell(297, 10, utf8_decode(strtoupper($nombre_curso)), 0, 1, 'C');

    // DNI
    $pdf->SetFont('Helvetica', '', 14);
    $pdf->SetXY(0, 98);
    $pdf->Cell(297, 10, utf8_decode($dni), 0, 1, 'C');

    // Carga Horaria
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->SetXY(0, 132);
    $pdf->Cell(297, 10, utf8_decode($carga_horaria), 0, 1, 'C');

    // Año
    $pdf->SetFont('Helvetica', '', 12);
    $pdf->SetXY(0, 132);
    $pdf->Cell(297, 10, utf8_decode($anio_cursada), 0, 1, 'C');


    // Guardar el PDF
    $filename = $temp_dir . 'certificado_' . str_replace(' ', '_', $nombre_completo) . '_' . $id_curso . '.pdf';
    $pdf->Output('F', $filename);
    $pdf_files[] = $filename;
}

$_SESSION['pdf_files'] = $pdf_files;

// Limpiar datos de sesión que ya no se necesitan
unset($_SESSION['alumnos_para_certificar']);
unset($_SESSION['curso_info']);

header("Location: descargar_certificados.php");
exit();
?>