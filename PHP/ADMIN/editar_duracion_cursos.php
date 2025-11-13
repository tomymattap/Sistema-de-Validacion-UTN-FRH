<?php
session_start();
include("../conexion.php");

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../inicio_sesion.php?error=acceso_denegado');
    exit;
}

// Consulta para obtener los cursos y sus fechas
$consulta = "
    SELECT c.ID_Curso, c.Nombre_Curso, dc.Inicio_Curso, dc.Fin_Curso
    FROM curso c
    LEFT JOIN duracion_curso dc ON c.ID_Curso = dc.ID_Curso
    ORDER BY c.ID_Curso
";
$resultado = mysqli_query($conexion, $consulta);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Duración de Cursos - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_cursos.css">
</head>
<body class="fade-in">
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
            <div class="session-controls" id="session-controls"></div>
            <button class="hamburger-menu" aria-label="Abrir menú"><span></span><span></span><span></span></button>
        </div>
    </header>

    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav><ul></ul></nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <div class="contenido-principal">
                <div id="header-container">
                    <h1 class="main-title">Editar Fechas de Cursos</h1>
                    <a href="gestionar_cursos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
                </div>

                <form action="confirmar_modif_fechas.php" method="POST">
                    <div class="results-container">
                        <table id="tabla-fechas">
                            <thead>
                                <tr>
                                    <th>ID Curso</th>
                                    <th>Nombre del Curso</th>
                                    <th>Fecha de Inicio</th>
                                    <th>Fecha de Fin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                                    <?php while ($fila = mysqli_fetch_assoc($resultado)):
                                        $id_curso = htmlspecialchars($fila['ID_Curso']);
                                        $nombre_curso = htmlspecialchars($fila['Nombre_Curso']);
                                    ?>
                                        <tr data-id-curso="<?= $id_curso ?>">
                                            <td><?= $id_curso ?></td>
                                            <td><?= $nombre_curso ?></td>
                                            <td>
                                                <input type="hidden" name="cursos[<?= $id_curso ?>][nombre]" value="<?= $nombre_curso ?>">
                                                <input type="date" name="cursos[<?= $id_curso ?>][inicio]" value="<?= htmlspecialchars($fila['Inicio_Curso'] ?? '') ?>" class="date-input">
                                            </td>
                                            <td>
                                                <input type="date" name="cursos[<?= $id_curso ?>][fin]" value="<?= htmlspecialchars($fila['Fin_Curso'] ?? '') ?>" class="date-input">
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" style="text-align: center; padding: 2rem;">No se encontraron cursos.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-actions" style="text-align: right; margin-top: 20px;">
                        <button type="submit" id="btn-confirmar-fechas" class="btn-submit"><i class="fas fa-check"></i> Confirmar Modificaciones</button>
                    </div>
                </form>
            </div>
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
                        <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="../ALUMNO/perfil.php">Mi Perfil</a></li>
                        <br>
                        <li><a href="../ALUMNO/inscripciones.php">Inscripciones</a></li>
                        <br>
                        <li><a href="../ALUMNO/certificaciones.php">Certificaciones</a></li>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Resaltar fila al modificar una fecha
            document.querySelectorAll('.date-input').forEach(input => {
                input.addEventListener('change', function() {
                    this.closest('tr').classList.add('modified');
                });
            });

            // Script para manejar la sesión del usuario en el header
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
                                <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="../logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>`;
                    sessionHTML = `
                        <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    sessionControls.innerHTML = dropdownMenu;
                    mobileNav.innerHTML = sessionHTML;
                } else {
                    window.location.href = '../inicio_sesion.php';
                }
            });
        });
    </script>
</body>
</html>