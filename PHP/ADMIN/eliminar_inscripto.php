<?php
session_start();
include("../conexion.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_Inscripcion'])) {

    $id_admin = $_SESSION['user_id'];
    // ✅ Registrar el admin en MySQL para los triggers
    mysqli_query($conexion, "SET @current_admin = '$id_admin'");

    
    $id = intval($_POST['ID_Inscripcion']);
    $sql = "DELETE FROM inscripcion WHERE ID_Inscripcion = $id";
    if (mysqli_query($conexion, $sql)) {
        header('Location: gestionar_inscriptos.php');
        exit;
    } else {
        die('Error al eliminar: ' . mysqli_error($conexion));
    }
}

header('Location: gestionar_inscriptos.php');
exit;
?>