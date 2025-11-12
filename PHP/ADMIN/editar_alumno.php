<?php
include("../conexion.php");

// === SI LLEGA POR POST, ACTUALIZA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    header('Content-Type: application/json; charset=utf-8');
    
    $cuil = $_POST['ID_Cuil_Alumno'] ?? '';
    $nombre = $_POST['Nombre_Alumno'] ?? '';
    $apellido = $_POST['Apellido_Alumno'] ?? '';
    $email = $_POST['Email_Alumno'] ?? '';
    $direccion = $_POST['Direccion'] ?? '';
    $telefono = $_POST['Telefono'] ?? '';

    if ($cuil === '' || $nombre === '' || $apellido === '' || $email === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    $sql = "UPDATE alumno SET Nombre_Alumno=?, Apellido_Alumno=?, Email_Alumno=?, Direccion=?, Telefono=? WHERE ID_Cuil_Alumno=?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "ssssss", $nombre, $apellido, $email, $direccion, $telefono, $cuil);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Datos del alumno actualizados correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_stmt_error($stmt)]);
    }
    exit;
}

// === SI LLEGA POR GET, MUESTRA EL FORMULARIO ===
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
        exit('<p>No se encontró el alumno.</p>');
    }

    $alumno = mysqli_fetch_assoc($res);
} else {
    exit('<p>Error: Falta el ID de inscripción.</p>');
}
?>

<div class="edit-form-container">
    <h1 class="main-title">Editar Alumno</h1>
    <form method="post" action="../../PHP/ADMIN/editar_alumno.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="ID_Cuil_Alumno" value="<?= htmlspecialchars($alumno['ID_Cuil_Alumno']) ?>">

        <div class="form-group">
            <label>CUIL</label>
            <input type="text" value="<?= htmlspecialchars($alumno['ID_Cuil_Alumno']) ?>" readonly>
        </div>
        <div class="form-group">
            <label>DNI</label>
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
            <input type="text" name="Direccion" value="<?= htmlspecialchars($alumno['Direccion']) ?>">
        </div>
        <div class="form-group">
            <label>Teléfono</label>
            <input type="text" name="Telefono" value="<?= htmlspecialchars($alumno['Telefono']) ?>">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>
    </form>
</div>