<?php
session_start();
require '../conexion.php';

// --- Definición de rutas para el header/footer ---
$base_path = '../../';
$php_path = $base_path . 'PHP/';
$html_path = $base_path . 'HTML/';


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
    <link rel="icon" href="../Imagenes/icon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ALUMNO/encuesta.css">
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../../index.html">VALIDAR</a></li>
                    <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <!-- Dynamic content by JS -->
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>
        </div>
    </header>
    <main class="encuesta-page">
        <div class="encuesta-container">
            <?php if ($encuesta_completa): ?>
                <!-- VISTA SI LA ENCUESTA YA FUE COMPLETADA -->
                <div class="encuesta-completada">
                    <i class="fas fa-check-circle"></i>
                    <h1>¡Gracias por su opinión!</h1>
                    <p>Ya ha completado la encuesta de satisfacción para el curso "<?php echo htmlspecialchars($nombre_curso); ?>".</p>
                    <p>Ahora puede descargar su certificado.</p>
                    <div class="botones-verticales">
                        <a href="descargar_certificado.php?id=<?php echo $id_inscripcion; ?>" class="btn-descargar">
                            <i class="fas fa-download"></i> Descargar Certificado
                        </a>
                        <a href="certificaciones.php" class="btn-volver">Volver a mis certificaciones</a>
                    </div>
                </div>
            <?php else: ?>
                <!-- VISTA PARA MOSTRAR EL FORMULARIO DE LA ENCUESTA -->
                <a href="certificaciones.php" class="btn-volver-prominente"><i class="fas fa-arrow-left"></i> Volver sin contestar</a>
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
    <div class="footer-container">
        <div class="footer-logo-info">
            <img src="../../Imagenes/UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
            <div class="footer-info">
                <p>París 532, Haedo (1706)</p>
                <p>Buenos Aires, Argentina</p><br>
                <p>Número de teléfono del depto.</p><br>
                <p>extension@frh.utn.edu.ar</p>
            </div>
        </div>
        <div class="footer-social-legal">
                <div class="footer-social">
                    <a href="https://www.youtube.com/@facultadregionalhaedo-utn3647" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.linkedin.com/school/utn-facultad-regional-haedo/" target="_blank"><i class="fab fa-linkedin"></i></a>
                </div>
                <div class="footer-legal">
                    <a href="mailto:extension@frh.utn.edu.ar">Contacto</a>
                    <br> 
                    <a href="#politicas">Políticas de Privacidad</a>
                </div>
            </div>
        <div class="footer-separator"></div>
        <div class="footer-nav">
            <h4>Navegación</h4>
            <ul>
                <li><a href="../../index.html">Validar</a></li>
                <li><a href="../../HTML/sobre_nosotros.html">Sobre Nosotros</a></li>
                <li><a href="../../HTML/contacto.html">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-dynamic-nav">
            <?php if (isset($_SESSION['user_name'])): ?>
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Estudiante'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <br>
                        <li><a href="../ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="../ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="../ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="../ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <br>
                        <li><a href="inscripciones.php">Inscripciones</a></li>
                        <br>
                        <li><a href="certificaciones.php">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="../inicio_sesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    </footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../../JavaScript/general.js"></script>
    <script>
        fetch('../get_user_name.php')
            .then(response => response.json())
            .then(data => {
                const sessionControls = document.getElementById('session-controls');
                const mobileNav = document.querySelector('.off-canvas-menu nav ul');
                let sessionHTML = '';

                if (data.user_name && data.user_rol === 2) {
                    const dropdownMenu = `
                        <button class="user-menu-toggle">Hola, ${data.user_name}. <i class="fas fa-chevron-down"></i></button>
                        <div class="dropdown-menu">
                            <ul>
                                <li><a href="perfil.php">Mi Perfil</a></li>
                                <li><a href="inscripciones.php">Inscripciones</a></li>
                                <li><a href="certificaciones.php">Certificaciones</a></li>
                                <li><a href="../logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>`;
                    sessionHTML = `
                        <li><a href="perfil.php">Mi Perfil</a></li>
                        <li><a href="inscripciones.php">Inscripciones</a></li>
                        <li><a href="certificaciones.php">Certificaciones</a></li>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    
                    sessionControls.innerHTML = dropdownMenu;
                    mobileNav.insertAdjacentHTML('beforeend', sessionHTML);
                } else {
                    window.location.href = '../inicio_sesion.php?error=acceso_denegado';
                }
            });
    </script>
</body>
</html>