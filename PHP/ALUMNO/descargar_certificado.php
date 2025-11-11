<?php
session_start();
require '../conexion.php';

// --- BLOQUES DE SEGURIDAD ---
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    header("Location: ../inicio_sesion.php?error=acceso_denegado");
    exit();
}
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Error: ID de inscripción no válido.");
}

$id_inscripcion = $_GET['id'];
$user_id = $_SESSION['user_id'];

// 1. VERIFICAR PROPIEDAD Y OBTENER TIPO DE CURSO
$stmt_check = $conexion->prepare(
    "SELECT i.ID_Curso, c.Tipo 
     FROM inscripcion i 
     JOIN curso c ON i.ID_Curso = c.ID_Curso 
     WHERE i.ID_Inscripcion = ? AND i.ID_Cuil_Alumno = ?"
);
$stmt_check->bind_param("is", $id_inscripcion, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    die("Error: No tiene permiso para descargar este certificado.");
}
$curso_info = $result_check->fetch_assoc();
$stmt_check->close();

// 2. VERIFICAR QUE LA ENCUESTA ESTÉ COMPLETA (SOLO SI EL CURSO ES 'GENUINO')
if ($curso_info['Tipo'] === 'Genuino') {
    $stmt_encuesta = $conexion->prepare("SELECT ID_Encuesta FROM encuesta_satisfaccion WHERE ID_Inscripcion = ?");
    $stmt_encuesta->bind_param("i", $id_inscripcion);
    $stmt_encuesta->execute();
    if ($stmt_encuesta->get_result()->num_rows === 0) {
        // Si no está completa, lo redirigimos a la encuesta.
        header("Location: ver_certificado.php?id=" . $id_inscripcion);
        exit();
    }
    $stmt_encuesta->close();
}

// 3. BUSCAR Y ENTREGAR EL ARCHIVO PDF
$stmt_pdf = $conexion->prepare("SELECT ID_CUV FROM certificacion WHERE ID_Inscripcion_Certif = ?");
$stmt_pdf->bind_param("i", $id_inscripcion);
$stmt_pdf->execute();
$result_pdf = $stmt_pdf->get_result();
if ($row = $result_pdf->fetch_assoc()) {
    $cuv = $row['ID_CUV'];
    // Asumimos que los PDFs se guardan en una carpeta específica con un nombre predecible.
    // DEBES AJUSTAR ESTA RUTA Y LÓGICA DE NOMBRE DE ARCHIVO A TU SISTEMA.
    $file_path = "../ADMIN/cert_generated/" . $cuv . ".pdf";

    if (file_exists($file_path)) {
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    }
}

die("El archivo del certificado no fue encontrado. Por favor, contacte a la administración.");
?>