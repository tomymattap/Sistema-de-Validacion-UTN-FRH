<?php
session_start();
include("../conexion.php");

$curso = null;
$cursos_a_eliminar = [];
$error_message = '';

// Manejar eliminación múltiple desde POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cursos_a_eliminar'])) {
    $ids_cursos = $_POST['cursos_a_eliminar'];
    if (!empty($ids_cursos)) {
        $placeholders = implode(',', array_fill(0, count($ids_cursos), '?'));
        $types = str_repeat('i', count($ids_cursos));
        $sql = "SELECT ID_Curso, Nombre_Curso, Categoria FROM curso WHERE ID_Curso IN ($placeholders)";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, $types, ...$ids_cursos);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $cursos_a_eliminar[] = $fila;
        }
    }
// Manejar eliminación única desde GET
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    $id_curso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if ($id_curso) {
        $sql = "SELECT ID_Curso, Nombre_Curso, Categoria FROM curso WHERE ID_Curso = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_curso);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        if ($fila = mysqli_fetch_assoc($resultado)) {
            $cursos_a_eliminar[] = $fila;
        }
    } else {
        $error_message = 'ID de curso no válido.';
    }
}

if (empty($cursos_a_eliminar) && empty($error_message)) {
    $error_message = 'No se ha seleccionado ningún curso para eliminar.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Eliminación - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Public Sans', sans-serif; }
        .confirmation-container { max-width: 600px; margin: 4rem auto; background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        .confirmation-icon { font-size: 3rem; color: var(--color-secundario-4); margin-bottom: 1rem; }
        .confirmation-title { font-size: 1.8rem; margin-bottom: 1rem; color: var(--color-principal); }
        .confirmation-text { font-size: 1.1rem; margin-bottom: 1.5rem; }
        .course-details { background-color: #f1f1f1; padding: 1rem; border-radius: 6px; text-align: left; margin-bottom: 2rem; }
        .course-details p { margin: 0.5rem 0; }
        .confirmation-actions { display: flex; justify-content: center; gap: 1rem; }
        .lista-cursos-eliminar { list-style: none; padding: 0; margin-bottom: 2rem; text-align: left; }
        .lista-cursos-eliminar li {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0.5rem;
            border-radius: 4px;
            font-size: 1rem;
        }
        .btn { padding: 0.8rem 1.5rem; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
        .btn-danger { background-color: var(--color-secundario-4); color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }
    </style>
</head>
<body class="fade-in">
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
        </div>
    </header>

    <main>
        <div class="confirmation-container">
            <?php if (!empty($error_message)): ?>
                <div class="confirmation-icon"><i class="fas fa-times-circle"></i></div>
                <h1 class="confirmation-title">Error</h1>
                <p class="confirmation-text"><?= htmlspecialchars($error_message) ?></p>
                <div class="confirmation-actions">
                    <a href="filtrar_cursos.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            <?php else: ?>
                <div class="confirmation-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <h1 class="confirmation-title">Confirmar Eliminación</h1>
                <p class="confirmation-text">¿Está absolutamente seguro de que desea eliminar los siguientes <strong><?= count($cursos_a_eliminar) ?></strong> curso(s)? Esta acción no se puede deshacer.</p>
                
                <ul class="lista-cursos-eliminar">
                    <?php foreach ($cursos_a_eliminar as $curso): ?>
                        <li><strong><?= htmlspecialchars($curso['Nombre_Curso']) ?></strong> (ID: <?= htmlspecialchars($curso['ID_Curso']) ?>)</li>
                    <?php endforeach; ?>
                </ul>

                <div class="confirmation-actions">
                    <form action="eliminar_curso.php" method="POST" style="margin:0;">
                        <?php foreach ($cursos_a_eliminar as $curso): ?>
                            <input type="hidden" name="cursos_a_eliminar[]" value="<?= htmlspecialchars($curso['ID_Curso']) ?>">
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Sí, eliminar</button>
                    </form>
                    <a href="filtrar_cursos.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script src="../../JavaScript/general.js"></script>
    <script>
        fetch('../get_user_name.php')
            .then(response => response.json())
            .then(data => {
                if (!data.user_name || data.user_rol !== 1) {
                    window.location.href = '../../HTML/iniciosesion.html';
                }
            });
    </script>
</body>
</html>
