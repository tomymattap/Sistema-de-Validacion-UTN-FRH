<?php
session_start();
include("../conexion.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_Curso'])) {

    $id_admin = $_SESSION['user_id'];
    // ✅ Registrar el admin en MySQL para los triggers
    mysqli_query($conexion, "SET @current_admin = '$id_admin'");
    $id_curso = filter_input(INPUT_POST, 'ID_Curso', FILTER_VALIDATE_INT);

    if ($id_curso) {
        // Iniciar transacción para asegurar la integridad de los datos
        mysqli_begin_transaction($conexion);

        try {
            // 1. Verificar si hay inscripciones asociadas a este curso
            $check_sql = "SELECT COUNT(*) as count FROM inscripcion WHERE ID_Curso = ?";
            $stmt_check = mysqli_prepare($conexion, $check_sql);
            mysqli_stmt_bind_param($stmt_check, "i", $id_curso);
            mysqli_stmt_execute($stmt_check);
            $result_check = mysqli_stmt_get_result($stmt_check);
            $row = mysqli_fetch_assoc($result_check);

            if ($row['count'] > 0) {
                // Si hay inscripciones, no se puede eliminar.
                throw new Exception("No se puede eliminar el curso porque tiene " . $row['count'] . " inscripciones asociadas. Por favor, elimine o reasigne primero las inscripciones.");
            }

            // 2. Si no hay inscripciones, proceder a eliminar el curso
            $delete_sql = "DELETE FROM curso WHERE ID_Curso = ?";
            $stmt_delete = mysqli_prepare($conexion, $delete_sql);
            mysqli_stmt_bind_param($stmt_delete, "i", $id_curso);
            mysqli_stmt_execute($stmt_delete);

            // Confirmar la transacción
            mysqli_commit($conexion);
            header('Location: gestionar_cursos.php?status=deleted');
            exit;

        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            mysqli_rollback($conexion);
            die('Error: ' . $e->getMessage() . ' <a href="gestionar_cursos.php">Volver</a>');
        }
    }
}

header('Location: gestionar_cursos.php');
exit;
?>