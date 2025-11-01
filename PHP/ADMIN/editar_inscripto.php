<?php
include("../conexion.php");

// Proceso de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $id = intval($_POST['ID_Inscripcion']);
    $ID_Curso = intval($_POST['ID_Curso']);
    $Cuatrimestre = mysqli_real_escape_string($conexion, $_POST['Cuatrimestre']);
    $Anio = intval($_POST['Anio']);
    $Estado_Cursada = mysqli_real_escape_string($conexion, $_POST['Estado_Cursada']);

    $sql = "UPDATE inscripcion SET ID_Curso = ?, Cuatrimestre = ?, Anio = ?, Estado_Cursada = ? WHERE ID_Inscripcion = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "isisi", $ID_Curso, $Cuatrimestre, $Anio, $Estado_Cursada, $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header('Location: verinscriptos.php?update=success');
        exit;
    } else {
        die('Error al actualizar: ' . mysqli_error($conexion));
    }
}

// Mostrar formulario de edición
if (($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'GET') && isset($_REQUEST['ID_Inscripcion'])) {
    $id = intval($_REQUEST['ID_Inscripcion']);
    $q = "SELECT i.*, a.Nombre_Alumno, a.Apellido_Alumno FROM inscripcion i
           JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
           WHERE i.ID_Inscripcion = ?";
    $stmt = mysqli_prepare($conexion, $q);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) == 0) { 
        header('Location: verinscriptos.php?error=notfound');
        exit;
    }
    $inscripcion = mysqli_fetch_assoc($res);

    $cursos_res = mysqli_query($conexion, "SELECT ID_Curso, Nombre_Curso FROM curso ORDER BY Nombre_Curso");
    $estados = ['En curso', 'Finalizado', 'CERTIFICADA', 'ASISTIDO'];
} else {
    header('Location: verinscriptos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Inscripción - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/verinscriptos.css">
    <style>
        .edit-form-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background-color: #f8f9fa; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 700; color: var(--color-principal); }
        .form-group input, .form-group select, .form-group p { width: 100%; padding: 0.75rem; border: 1px solid #ced4da; border-radius: 6px; font-size: 1rem; }
        .form-group p { background-color: #e9ecef; }
        .form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
        .form-actions button, .form-actions a { padding: 0.75rem 1.5rem; border: none; border-radius: 6px; font-size: 1rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; cursor: pointer; }
        .btn-submit { background-color: var(--color-secundario-2); color: white; }
        .btn-submit:hover { background-color: #7ab831; }
        .btn-cancel { background-color: #6c757d; color: white; }
        .btn-cancel:hover { background-color: #5a6268; }
    </style>
</head>
<body class="fade-in">
    <div class="preloader"><div class="spinner"></div></div>

    <header class="site-header">
        <!-- Header content remains the same -->
    </header>

<main>
<section class="admin-section">
<div class="admin-container">
    <div class="edit-form-container">
        <h1 class="main-title">Editar Inscripción <span style="color: var(--color-secundario-1);">#<?= htmlspecialchars($inscripcion['ID_Inscripcion']) ?></span></h1>
        <form method="post" action="editar_inscripto.php">
            <input type="hidden" name="ID_Inscripcion" value="<?= htmlspecialchars($inscripcion['ID_Inscripcion'])?> ">
            
            <div class="form-group">
                <label>Alumno</label>
                <p><?= htmlspecialchars($inscripcion['Apellido_Alumno'] . ', ' . $inscripcion['Nombre_Alumno']) ?></p>
            </div>

            <div class="form-group">
                <label for="ID_Curso">Curso</label>
                <select name="ID_Curso" id="ID_Curso" required>
                    <?php while($c = mysqli_fetch_assoc($cursos_res)): 
                        $selected = ($c['ID_Curso'] == $inscripcion['ID_Curso']) ? 'selected' : '';?>
                        <option value="<?= $c['ID_Curso'] ?>" <?= $selected ?>><?= htmlspecialchars($c['Nombre_Curso']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="Cuatrimestre">Periodo</label>
                <input type="text" name="Cuatrimestre" id="Cuatrimestre" value="<?= htmlspecialchars($inscripcion['Cuatrimestre']) ?>" required>
            </div>

            <div class="form-group">
                <label for="Anio">Año</label>
                <input type="number" name="Anio" id="Anio" value="<?= htmlspecialchars($inscripcion['Anio']) ?>" required>
            </div>

            <div class="form-group">
                <label for="Estado_Cursada">Estado de Cursada</label>
                <select name="Estado_Cursada" id="Estado_Cursada" required>
                    <?php foreach($estados as $est): 
                        $selected = ($est == $inscripcion['Estado_Cursada']) ? 'selected' : '';?>
                        <option value="<?= $est ?>" <?= $selected ?>><?= $est ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-actions">
                <a href="verinscriptos.php" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</a>
                <button type="submit" name="action" value="update" class="btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
</section>
</main>

<footer class="site-footer">
    <!-- Footer content -->
</footer>

    <script src="../../JavaScript/general.js"></script>
    <a href="#" class="scroll-to-top-btn" title="Volver arriba"><i class="fas fa-arrow-up"></i></a>
</body>
</html>