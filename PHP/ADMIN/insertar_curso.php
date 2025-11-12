<?php
session_start();
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id_admin = $_SESSION['user_id'];
    // ✅ Registrar el admin en MySQL para los triggers
    mysqli_query($conexion, "SET @current_admin = '$id_admin'");

    // Recoger los datos del formulario
    $nombre_curso = $_POST['nombre_curso'] ?? '';
    $modalidad = $_POST['modalidad'] ?? null;
    $docente = $_POST['docente'] ?? null;
    $carga_horaria = $_POST['carga_horaria'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $requisitos = $_POST['requisitos'] ?? null;
    $categoria = $_POST['categoria'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    // Validar datos requeridos
    if (empty($nombre_curso) || empty($categoria) || empty($tipo)) {
        die('Error: Faltan datos obligatorios (Nombre, Categoría o Tipo).');
    }

    // Preparar la consulta SQL para evitar inyección SQL
    $sql = "INSERT INTO curso (Nombre_Curso, Modalidad, Docente, Carga_Horaria, Descripcion, Requisitos, Categoria, Tipo) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conexion, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $nombre_curso, $modalidad, $docente, $carga_horaria, $descripcion, $requisitos, $categoria, $tipo);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirigir a la página de gestión de cursos si la inserción fue exitosa
            header('Location: gestionar_cursos.php?status=success');
            exit;
        } else {
            die('Error al guardar el curso: ' . mysqli_stmt_error($stmt));
        }
    } else {
        die('Error al preparar la consulta: ' . mysqli_error($conexion));
    }
}
?>