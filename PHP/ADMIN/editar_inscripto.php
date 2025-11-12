<?php
session_start();
include("../conexion.php");

// === SI LLEGA POR POST, ACTUALIZA ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    header('Content-Type: application/json; charset=utf-8');

    $id = intval($_POST['ID_Inscripcion'] ?? 0);
    $ID_Curso = intval($_POST['ID_Curso'] ?? 0);
    $Cuatrimestre = $_POST['Cuatrimestre'] ?? '';
    $Anio = intval($_POST['Anio'] ?? 0);
    $Estado_Cursada = $_POST['Estado_Cursada'] ?? '';

    if ($id <= 0 || $ID_Curso <= 0 || $Cuatrimestre === '' || $Anio <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    $sql = "UPDATE inscripcion SET ID_Curso = ?, Cuatrimestre = ?, Anio = ?, Estado_Cursada = ? WHERE ID_Inscripcion = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "isisi", $ID_Curso, $Cuatrimestre, $Anio, $Estado_Cursada, $id);

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true, 'message' => 'Inscripción actualizada correctamente.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . mysqli_error($conexion)]);
    }
    exit;
}

// === SI LLEGA POR GET, MUESTRA EL FORMULARIO ===
if (isset($_GET['ID_Inscripcion'])) {
    $id = intval($_GET['ID_Inscripcion']);
    $q = "SELECT i.*, a.Nombre_Alumno, a.Apellido_Alumno FROM inscripcion i
          JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
          WHERE i.ID_Inscripcion = ?";
    $stmt = mysqli_prepare($conexion, $q);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($res) === 0) {
        exit('<p>No se encontró la inscripción.</p>');
    }

    $inscripcion = mysqli_fetch_assoc($res);
    $cursos_res = mysqli_query($conexion, "SELECT ID_Curso, Nombre_Curso FROM curso ORDER BY Nombre_Curso");
    $estados = ['En curso', 'Finalizado', 'CERTIFICADA', 'ASISTIDO'];
} else {
    exit('<p>Error: Falta el ID de inscripción.</p>');
}
?>

<div class="edit-form-container">
    <h1 class="main-title">Editar Inscripción <span style="color: var(--color-secundario-1);">#<?= htmlspecialchars($inscripcion['ID_Inscripcion']) ?></span></h1>
    <form method="post" action="../../PHP/ADMIN/editar_inscripto.php">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="ID_Inscripcion" value="<?= htmlspecialchars($inscripcion['ID_Inscripcion']) ?>">
        
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
            <select name="Cuatrimestre" id="Cuatrimestre" required>
                <?php
                $cuatrimestres_options = ['Primer Cuatrimestre', 'Segundo Cuatrimestre', 'Anual'];
                foreach ($cuatrimestres_options as $option) {
                    $selected = ($option == $inscripcion['Cuatrimestre']) ? 'selected' : '';
                    echo "<option value=\"{$option}\" {$selected}>{$option}</option>";
                }
                ?>
            </select>
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
            <button type="submit" class="btn-submit">Guardar Cambios</button>
        </div>
    </form>
</div>