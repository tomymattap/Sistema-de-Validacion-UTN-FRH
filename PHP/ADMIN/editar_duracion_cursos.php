<?php
session_start();
include("../conexion.php");

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
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
    <link rel="stylesheet" href="../../CSS/gestionar_cursos.css">
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
                    <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
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
                    <a href="gestionar_cursos.php" class="menu-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
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

    <footer class="site-footer"></footer>
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
                                <li><a href="gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                                <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="../logout.php">Cerrar Sesión</a></li>
                            </ul>
                        </div>`;
                    sessionHTML = `
                        <li><a href="gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                        <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    sessionControls.innerHTML = dropdownMenu;
                    mobileNav.innerHTML = sessionHTML;
                } else {
                    window.location.href = '../iniciosesion.php';
                }
            });
        });
    </script>
</body>
</html>