<?php
session_start();
include("../conexion.php");


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cursos_a_eliminar'])) {

    $id_admin = $_SESSION['user_id'];
    // ✅ Registrar el admin en MySQL para los triggers
    mysqli_query($conexion, "SET @current_admin = '$id_admin'");

    $ids_a_eliminar = $_POST['cursos_a_eliminar'];

    if (!empty($ids_a_eliminar)) {
        // Iniciar transacción para asegurar la integridad de los datos
        mysqli_begin_transaction($conexion);

        try {
            foreach ($ids_a_eliminar as $id_curso) {
                $id_curso = intval($id_curso);

                // 1. Verificar si hay inscripciones asociadas a este curso
                $check_sql = "SELECT COUNT(*) as count FROM inscripcion WHERE ID_Curso = ?";
                $stmt_check = mysqli_prepare($conexion, $check_sql);
                mysqli_stmt_bind_param($stmt_check, "i", $id_curso);
                mysqli_stmt_execute($stmt_check);
                $result_check = mysqli_stmt_get_result($stmt_check);
                $row = mysqli_fetch_assoc($result_check);

                if ($row['count'] > 0) {
                    // Si hay inscripciones, no se puede eliminar y se revierte todo.
                    throw new Exception("No se puede eliminar el curso con ID $id_curso porque tiene " . $row['count'] . " inscripciones asociadas. La operación ha sido cancelada.");
                }

                // 2. Si no hay inscripciones, proceder a eliminar el curso
                $delete_sql = "DELETE FROM curso WHERE ID_Curso = ?";
                $stmt_delete = mysqli_prepare($conexion, $delete_sql);
                mysqli_stmt_bind_param($stmt_delete, "i", $id_curso);
                if (!mysqli_stmt_execute($stmt_delete)) {
                    throw new Exception("Error al eliminar el curso con ID $id_curso.");
                }
            }

            // Confirmar la transacción si todo fue exitoso
            mysqli_commit($conexion);
            header('Location: gestionar_cursos.php?status=deleted_multiple');
            exit;

        } catch (Exception $e) {
            // Revertir la transacción en caso de error
            mysqli_rollback($conexion);
            die('Error: ' . $e->getMessage() . ' <a href="gestionar_cursos.php">Volver</a>');
        }
    }
}

header('Location: filtrar_cursos.php?error=no_selection');
exit;
?>