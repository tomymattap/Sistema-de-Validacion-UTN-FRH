<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
    exit;
}

include("../conexion.php");

// Consulta para obtener las evaluaciones de cursos externos
$consulta_sql = "
    SELECT 
    e.ID_Evaluacion,
    c.Nombre_Curso,
    c.Descripcion,
    c.Carga_Horaria,
    c.Requisitos,
    e.Institucion1,
    e.Institucion2,
    e.Institucion3,
    e.Estado_Evaluacion,
    e.Archivo_Evaluacion
FROM evaluacion_curso_externo e
JOIN curso c ON e.ID_Curso = c.ID_Curso
WHERE c.Tipo = 'CERTIFICACIÓN'
ORDER BY e.ID_Evaluacion DESC;

";

$resultado = mysqli_query($conexion, $consulta_sql);

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Cursos Externos - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>gestionar_cursos.css">
</head>
<body class="fade-in">

<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a>
        </div>
        <nav class="main-nav hide-on-mobile">
            <ul>
                <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
                <li><a href="<?php echo $html_path; ?>sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
        <div class="session-controls" id="session-controls"></div>
        <button class="hamburger-menu" aria-label="Abrir menú">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
</header>

<div class="off-canvas-menu" id="off-canvas-menu">
    <button class="close-btn" aria-label="Cerrar menú">&times;</button>
    <nav>
        <ul>
            <!-- Contenido dinámico por JS -->
        </ul>
    </nav>
</div>

<main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
    <div class="gestion-cursos-container">
        <div class="contenido-principal">
            <div id="header-container">
                <h1 class="main-title">Gestión de Cursos Externos</h1>
                <div class="header-buttons">
                    <a href="gestionar_cursos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
                    <a href="generar_link.php" class="menu-btn"><i class="fas fa-share-alt"></i> COMPARTIR FORMULARIO</a>
                </div>
            </div>

            <div >
                <table id="tabla-cursos-externos">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Carga Horaria</th>
                            <th>Descripción</th>
                            <th>Requisitos</th>
                            <th colspan="3" class="inst-header">Instituciones Asociadas</th>
                            <th>Estado Evaluación</th>
                            <th>Info. Extra</th>
                            <th>Acción</th>
                        </tr>
                        <tr class="sub-header">
                            <th colspan="4"></th>
                            <th>Institución 1</th>
                            <th>Institución 2</th>
                            <th>Institución 3</th>
                            <th colspan="3"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)):
                                $estado_evaluacion = htmlspecialchars($fila['Estado_Evaluacion']);
                                $estado_class = '';

                                switch ($estado_evaluacion) {
                                    case 'PENDIENTE':
                                        $estado_class = 'estado-pendiente';
                                        break;
                                    case 'ACEPTADO':
                                        $estado_class = 'estado-aceptado';
                                        break;
                                    case 'RECHAZADO':
                                        $estado_class = 'estado-rechazado';
                                        break;
                                }
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($fila['Nombre_Curso']); ?></td>
                                <td><?= htmlspecialchars($fila['Carga_Horaria']); ?> hs</td>
                                <td><?= htmlspecialchars($fila['Descripcion']); ?></td>
                                <td><?= htmlspecialchars($fila['Requisitos']); ?></td>

                                <td class="col-institucion"><?= htmlspecialchars($fila['Institucion1']); ?></td>
                                <td class="col-institucion"><?= htmlspecialchars($fila['Institucion2']); ?></td>
                                <td class="col-institucion"><?= htmlspecialchars($fila['Institucion3']); ?></td>

                                <td class="col-estado <?= $estado_class ?>"><?= $estado_evaluacion ?></td>

                                <td class="col-info-extra">
                                    <?php if (!empty($fila['Archivo_Evaluacion'])): ?>
                                        <a href="descargar_archivo_externo.php?id=<?= htmlspecialchars($fila['ID_Evaluacion']) ?>" class="btn-descargar-pdf">
                                            <i class="fas fa-file-download"></i> PDF
                                        </a>
                                    <?php else: ?>
                                        <span class="sin-archivo">Sin archivo</span>
                                    <?php endif; ?>
                                </td>
                                <td class="col-accion">
                                    <a href="ver_evaluacion_externa.php?id=<?= htmlspecialchars($fila['ID_Evaluacion']) ?>" class="btn-accion" title="Ver y Evaluar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>

                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="sin-resultados">No se encontraron evaluaciones de cursos externos.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>
</main>

<footer class="site-footer">
    <!-- Contenido del footer -->
</footer>

<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
    <i class="fas fa-arrow-up"></i>
</a>

<script src="<?php echo $js_path; ?>general.js"></script>
<script>
    fetch('../get_user_name.php')
        .then(response => response.json())
        .then(data => {
            const sessionControls = document.getElementById('session-controls');
            const mobileNav = document.querySelector('.off-canvas-menu nav ul');
            let sessionHTML = '';

            if (data.user_name && data.user_rol === 1) {
                const dropdownMenu = `
                    <button class="user-menu-toggle">Hola, ${data.user_name}. <i class="fas fa-chevron-down"></i></button>
                    <div class="dropdown-menu">
                        <ul>
                            <li><a href="gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="gestionaradmin.php">Gestionar Administradores</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>`;
                sessionHTML = `
                    <li><a href="gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                    <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                    <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                sessionControls.innerHTML = dropdownMenu;
                mobileNav.innerHTML += sessionHTML; // Usar += para no sobreescribir los links estáticos
            } else {
                window.location.href = '../iniciosesion.php?error=acceso_denegado';
            }
        });
</script>
</body>
</html>