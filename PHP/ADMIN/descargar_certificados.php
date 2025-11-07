<?php
session_start();

if (!isset($_SESSION['pdf_files']) || empty($_SESSION['pdf_files'])) {
    die("No hay archivos de certificado para descargar.");
}

$pdf_files = $_SESSION['pdf_files'];

$zip = new ZipArchive();
$zip_name = tempnam(sys_get_temp_dir(), 'certificados') . ".zip";

if ($zip->open($zip_name, ZipArchive::CREATE) !== TRUE) {
    die("No se pudo crear el archivo zip.");
}

foreach ($pdf_files as $file) {
    if (file_exists($file)) {
        $zip->addFile($file, basename($file));
    }
}

$zip->close();

// Limpiar los archivos PDF temporales
foreach ($pdf_files as $file) {
    if (file_exists($file)) {
        unlink($file);
    }
}

// Limpiar el directorio temporal si está vacío
$temp_dir = dirname($pdf_files[0]);
if (is_dir($temp_dir) && count(scandir($temp_dir)) == 2) { // . y ..
    rmdir($temp_dir);
}

// Limpiar la sesión
unset($_SESSION['pdf_files']);

// Enviar el zip para descarga
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=certificados.zip");
header("Content-Length: " . filesize($zip_name));
readfile($zip_name);

// Eliminar el archivo zip temporal
unlink($zip_name);

exit();
?>