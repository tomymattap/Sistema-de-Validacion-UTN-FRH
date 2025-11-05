<?php
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_Inscripcion'])) {
    $id = intval($_POST['ID_Inscripcion']);
    $sql = "DELETE FROM inscripcion WHERE ID_Inscripcion = $id";
    if (mysqli_query($conexion, $sql)) {
        header('Location: gestionarinscriptos.php');
        exit;
    } else {
        die('Error al eliminar: ' . mysqli_error($conexion));
    }
}

header('Location: gestionarinscriptos.php');
exit;
?>