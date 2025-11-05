<?php
include("../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ID_Curso'])) {
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
            
            // Mostrar un mensaje de error amigable usando la plantilla
            $page_title = 'Error al Eliminar Curso';
            include('../header.php');
            echo '<main class="admin-section" style="padding: 2rem;">';
            echo '<div class="admin-container">';
            echo '<h1 class="main-title">Error al eliminar el curso</h1>';
            echo '<div class="error-message" style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">';
            echo htmlspecialchars($e->getMessage());
            echo '</div>';
            echo '<a href="gestionar_cursos.php" class="btn" style="background-color: var(--color-principal); color: white;">Volver a Gestión de Cursos</a>';
            echo '</div></main>';
            include('../footer.php');
            exit;
        }
    }
}

header('Location: gestionar_cursos.php');
exit;
?>