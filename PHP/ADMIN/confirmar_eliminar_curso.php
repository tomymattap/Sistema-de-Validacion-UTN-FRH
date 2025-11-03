<?php
session_start();
include("../conexion.php");

$curso = null;
$id_curso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if ($id_curso) {
    $sql = "SELECT ID_Curso, Nombre_Curso, Categoria FROM curso WHERE ID_Curso = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_curso);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $curso = mysqli_fetch_assoc($resultado);
    } else {
        die('Curso no encontrado.');
    }
} else {
    die('ID de curso no válido.');
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
        body { background-color: #f8f9fa; }
        .confirmation-container { max-width: 600px; margin: 4rem auto; background-color: #fff; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); text-align: center; }
        .confirmation-icon { font-size: 3rem; color: var(--color-secundario-4); margin-bottom: 1rem; }
        .confirmation-title { font-size: 1.8rem; margin-bottom: 1rem; color: var(--color-principal); }
        .confirmation-text { font-size: 1.1rem; margin-bottom: 1.5rem; }
        .course-details { background-color: #f1f1f1; padding: 1rem; border-radius: 6px; text-align: left; margin-bottom: 2rem; }
        .course-details p { margin: 0.5rem 0; }
        .confirmation-actions { display: flex; justify-content: center; gap: 1rem; }
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
            <div class="confirmation-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <h1 class="confirmation-title">Confirmar Eliminación</h1>
            <p class="confirmation-text">¿Está absolutamente seguro de que desea eliminar el siguiente curso? Esta acción no se puede deshacer.</p>
            
            <div class="course-details">
                <p><strong>ID:</strong> <?= htmlspecialchars($curso['ID_Curso']) ?></p>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($curso['Nombre_Curso']) ?></p>
                <p><strong>Categoría:</strong> <?= htmlspecialchars($curso['Categoria']) ?></p>
            </div>

            <div class="confirmation-actions">
                <form action="eliminar_curso.php" method="POST" style="margin:0;">
                    <input type="hidden" name="ID_Curso" value="<?= htmlspecialchars($curso['ID_Curso']) ?>">
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt"></i> Sí, eliminar</button>
                </form>
                <a href="gestionar_cursos.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
            </div>
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


