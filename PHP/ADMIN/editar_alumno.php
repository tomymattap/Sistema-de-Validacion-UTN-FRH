<?php
include("../conexion.php");

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $cuil = mysqli_real_escape_string($conexion, $_POST['ID_Cuil_Alumno']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['Nombre_Alumno']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['Apellido_Alumno']);
    $email = mysqli_real_escape_string($conexion, $_POST['Email_Alumno']);
    $direccion = mysqli_real_escape_string($conexion, $_POST['Direccion_Alumno']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['Telefono_Alumno']);

    $sql = "UPDATE alumno 
            SET Nombre_Alumno = ?, Apellido_Alumno = ?, Email_Alumno = ?, Direccion = ?, Telefono = ?
            WHERE ID_Cuil_Alumno = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $nombre, $apellido, $email, $direccion, $telefono, $cuil);

    if (mysqli_stmt_execute($stmt)) {
        header('Location: gestionarinscriptos.php?update=success');
        exit;
    } else {
        die('Error al actualizar: ' . mysqli_error($conexion));
    }
}

// Mostrar formulario de edición
if (isset($_GET['ID_Inscripcion'])) {
    $idInscripcion = intval($_GET['ID_Inscripcion']);
    $query = "SELECT a.* FROM alumno a
              JOIN inscripcion i ON a.ID_Cuil_Alumno = i.ID_Cuil_Alumno
              WHERE i.ID_Inscripcion = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $idInscripcion);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) == 0) {
        header('Location: gestionarinscriptos.php?error=notfound');
        exit;
    }

    $alumno = mysqli_fetch_assoc($res);
} else {
    header('Location: gestionarinscriptos.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Alumno - Admin</title>
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/verinscriptos.css">
    <style>
        .edit-form-container { max-width: 800px; margin: 2rem auto; padding: 2rem; background: #f8f9fa; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: .5rem; font-weight: 700; color: var(--color-principal); }
        .form-group input { width: 100%; padding: .75rem; border: 1px solid #ced4da; border-radius: 6px; }
        .form-actions { display: flex; justify-content: flex-end; gap: 1rem; margin-top: 2rem; }
        .btn-submit { background: var(--color-secundario-2); color: #fff; padding: .75rem 1.5rem; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; }
        .btn-submit:hover { background: #7ab831; }
        .btn-cancel { background: #6c757d; color: #fff; padding: .75rem 1.5rem; border: none; border-radius: 6px; text-decoration: none; display: inline-block; }
        .btn-cancel:hover { background: #5a6268; }
        input[readonly] { background-color: #e9ecef; cursor: not-allowed; }
    </style>
</head>
<body class="fade-in">

<main>
<section class="admin-section">
<div class="admin-container">
    <div class="edit-form-container">
        <h1 class="main-title">Editar Alumno</h1>
        <form method="post" action="editar_alumno.php">
            <input type="hidden" name="ID_Cuil_Alumno" value="<?= htmlspecialchars($alumno['ID_Cuil_Alumno']) ?>">
            <div class="form-group">
                <label>CUIL (no editable)</label>
                <input type="text" value="<?= htmlspecialchars($alumno['ID_Cuil_Alumno']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>DNI (no editable)</label>
                <input type="text" value="<?= htmlspecialchars($alumno['DNI_Alumno']) ?>" readonly>
            </div>
            <div class="form-group">
                <label>Nombre</label>
                <input type="text" name="Nombre_Alumno" value="<?= htmlspecialchars($alumno['Nombre_Alumno']) ?>" required>
            </div>
            <div class="form-group">
                <label>Apellido</label>
                <input type="text" name="Apellido_Alumno" value="<?= htmlspecialchars($alumno['Apellido_Alumno']) ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="Email_Alumno" value="<?= htmlspecialchars($alumno['Email_Alumno']) ?>" required>
            </div>
            <div class="form-group">
                <label>Dirección</label>
                <input type="text" name="Direccion_Alumno" value="<?= htmlspecialchars($alumno['Direccion']) ?>">
            </div>
            <div class="form-group">
                <label>Teléfono</label>
                <input type="text" name="Telefono_Alumno" value="<?= htmlspecialchars($alumno['Telefono']) ?>">
            </div>
            <div class="form-actions">
                <a href="gestionarinscriptos.php" class="btn-cancel">Cancelar</a>
                <button type="submit" name="action" value="update" class="btn-submit">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
</section>
</main>
</body>
</html>