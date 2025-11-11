<?php
session_start();
require '../conexion.php';

// --- BLOQUES DE SEGURIDAD ---
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    header("Location: ../inicio_sesion.php?error=acceso_denegado");
    exit();
}
if (isset($_SESSION['force_password_change'])) {
    header('Location: cambiar_contrasena_obligatorio.php');
    exit();
}
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Error: ID de inscripción no válido.");
}

$id_inscripcion = $_GET['id'];
$user_id = $_SESSION['user_id'];

// --- VERIFICAR PROPIEDAD DE LA INSCRIPCIÓN ---
$stmt_check = $conexion->prepare("SELECT ID_Curso FROM inscripcion WHERE ID_Inscripcion = ? AND ID_Cuil_Alumno = ?");
$stmt_check->bind_param("is", $id_inscripcion, $user_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();
if ($result_check->num_rows === 0) {
    die("Error: No tiene permiso para acceder a esta certificación.");
}
$id_curso = $result_check->fetch_assoc()['ID_Curso'];
$stmt_check->close();

// --- VERIFICAR SI LA ENCUESTA YA FUE COMPLETADA ---
$stmt_encuesta = $conexion->prepare("SELECT ID_Encuesta FROM encuesta_satisfaccion WHERE ID_Inscripcion = ?");
$stmt_encuesta->bind_param("i", $id_inscripcion);
$stmt_encuesta->execute();
$encuesta_completa = $stmt_encuesta->get_result()->num_rows > 0;
$stmt_encuesta->close();

// --- OBTENER DATOS DEL CURSO PARA MOSTRAR ---
$stmt_curso = $conexion->prepare("SELECT Nombre_Curso FROM curso WHERE ID_Curso = ?");
$stmt_curso->bind_param("i", $id_curso);
$stmt_curso->execute();
$nombre_curso = $stmt_curso->get_result()->fetch_assoc()['Nombre_Curso'];
$stmt_curso->close();

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Encuesta de Satisfacción - UTN FRH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/encuesta.css">
</head>
<body>
    <header class="site-header">
        <!-- ... (código del header omitido por brevedad, es el mismo que en las otras páginas de alumno) ... -->
    </header>

    <main class="encuesta-page">
        <div class="encuesta-container">
            <a href="certificaciones.php" class="btn-volver-prominente"><i class="fas fa-arrow-left"></i> Volver sin contestar</a>
            <?php if ($encuesta_completa): ?>
                <!-- VISTA SI LA ENCUESTA YA FUE COMPLETADA -->
                <div class="encuesta-completada">
                    <i class="fas fa-check-circle"></i>
                    <h1>¡Gracias por su opinión!</h1>
                    <p>Ya ha completado la encuesta de satisfacción para el curso "<?php echo htmlspecialchars($nombre_curso); ?>".</p>
                    <p>Ahora puede descargar su certificado.</p>
                    <a href="descargar_certificado.php?id=<?php echo $id_inscripcion; ?>" class="btn-descargar">
                        <i class="fas fa-download"></i> Descargar Certificado
                    </a>
                    <a href="certificaciones.php" class="btn-volver">Volver a mis certificaciones</a>
                </div>
            <?php else: ?>
                <!-- VISTA PARA MOSTRAR EL FORMULARIO DE LA ENCUESTA -->
                <div class="encuesta-header">
                    <h1>Encuesta de Satisfacción</h1>
                    <p>Para descargar su certificado del curso "<strong><?php echo htmlspecialchars($nombre_curso); ?></strong>", por favor, complete la siguiente encuesta. Su opinión es muy valiosa para nosotros.</p>
                </div>
                <form action="guardar_encuesta.php" method="POST" class="encuesta-form">
                    <input type="hidden" name="id_inscripcion" value="<?php echo $id_inscripcion; ?>">
                    
                    <fieldset>
                        <legend>1. ¿Cómo consideraría el desempeño general de el/la/los formador/a/es?</legend>
                        <div class="radio-group">
                            <label><input type="radio" name="desempeno_formador" value="Muy bien" required> Muy bien</label>
                            <label><input type="radio" name="desempeno_formador" value="Bien"> Bien</label>
                            <label><input type="radio" name="desempeno_formador" value="Regular"> Regular</label>
                            <label><input type="radio" name="desempeno_formador" value="Malo"> Malo</label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>2. Respecto de la dinámica de la clase, ¿cómo evaluaría los siguientes aspectos?</legend>
                        <table class="matrix-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Muy bien</th>
                                    <th>Bien</th>
                                    <th>Regular</th>
                                    <th>Malo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $aspectos = [
                                    'claridad_temas' => 'Claridad de los temas planteados',
                                    'ejemplos_practicos' => 'Utilización de ejemplos prácticos',
                                    'respuesta_dudas' => 'Respuesta a dudas y consultas',
                                    'cumplimiento_horarios' => 'Cumplimiento de actividades y horarios'
                                ];
                                foreach ($aspectos as $key => $label): ?>
                                <tr>
                                    <td><?php echo $label; ?></td>
                                    <td><input type="radio" name="<?php echo $key; ?>" value="Muy bien" required></td>
                                    <td><input type="radio" name="<?php echo $key; ?>" value="Bien"></td>
                                    <td><input type="radio" name="<?php echo $key; ?>" value="Regular"></td>
                                    <td><input type="radio" name="<?php echo $key; ?>" value="Malo"></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </fieldset>

                    <fieldset>
                        <legend>3. ¿Cuál es su grado de satisfacción sobre el curso realizado?</legend>
                        <div class="escala-group">
                            <span>Muy Insatisfecho</span>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <label class="escala-item">
                                    <input type="radio" name="satisfaccion_curso" value="<?php echo $i; ?>" required>
                                    <span><?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                            <span>Muy satisfecho</span>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>4. ¿En qué medida ha contribuido el curso a sus tareas laborales?</legend>
                        <div class="escala-group">
                            <span>Muy poco</span>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <label class="escala-item">
                                    <input type="radio" name="contribucion_laboral" value="<?php echo $i; ?>" required>
                                    <span><?php echo $i; ?></span>
                                </label>
                            <?php endfor; ?>
                            <span>Mucho</span>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>5. ¿Recomendaría la capacitación de la FRH?</legend>
                        <div class="radio-group">
                            <label><input type="radio" name="recomienda_frh" value="Sí" required> Sí</label>
                            <label><input type="radio" name="recomienda_frh" value="No"> No</label>
                        </div>
                    </fieldset>

                    <fieldset>
                        <legend>6. ¿Le hubiese gustado que en la cursada se haya hablado de algún tema que no se habló? ¿Cuál?</legend>
                        <textarea name="tema_no_hablado" rows="4" placeholder="Su respuesta (opcional)"></textarea>
                    </fieldset>

                    <fieldset>
                        <legend>7. ¿Tiene alguna sugerencia para hacernos?</legend>
                        <textarea name="sugerencias" rows="4" placeholder="Su respuesta (opcional)"></textarea>
                    </fieldset>

                    <div class="form-actions">
                        <a href="certificaciones.php" class="btn-volver">Volver</a>
                        <button type="submit" class="submit-btn">Enviar Encuesta y Ver Certificado</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <footer class="site-footer">
        <!-- ... (código del footer omitido por brevedad) ... -->
    </footer>

    <script src="../../JavaScript/general.js"></script>
    <!-- ... (script de sesión de usuario omitido por brevedad) ... -->
</body>
</html>