<?php
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ID_Cuil_Alumno = isset($_POST['ID_Cuil_Alumno']) ? intval($_POST['ID_Cuil_Alumno']) : 0;
    $ID_Curso = isset($_POST['ID_Curso']) ? intval($_POST['ID_Curso']) : 0;
    $Cuatrimestre = isset($_POST['Cuatrimestre']) ? mysqli_real_escape_string($conexion, $_POST['Cuatrimestre']) : '';
    $Anio = isset($_POST['Anio']) ? intval($_POST['Anio']) : date('Y');
    $Estado_Cursada = isset($_POST['Estado_Cursada']) ? mysqli_real_escape_string($conexion, $_POST['Estado_Cursada']) : '';

    if ($ID_Cuil_Alumno && $ID_Curso) {
        $sql = "INSERT INTO inscripcion (ID_Cuil_Alumno, ID_Curso, Cuatrimestre, Anio, Estado_Cursada)
                VALUES ($ID_Cuil_Alumno, $ID_Curso, '$Cuatrimestre', $Anio, '$Estado_Cursada')";
        if (mysqli_query($conexion, $sql)) {
            header('Location: gestionarinscriptos.php');
            exit;
        } else {
            die('Error al insertar: ' . mysqli_error($conexion));
        }
    } else {
        die('Faltan datos obligatorios.');
    }
} else {
    header('Location: gestionarinscriptos.php');
    exit;
}
?>