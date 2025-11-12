<?php
session_start();
include("../conexion.php");

// Manejo de la búsqueda
$search_term = isset($_GET['search']) ? mysqli_real_escape_string($conexion, $_GET['search']) : '';
$where_sql = '';
if (!empty($search_term)) {
    // Busca coincidencias en el ID o en el nombre del curso
    $where_sql = "WHERE ID_Curso LIKE '%$search_term%' OR Nombre_Curso LIKE '%$search_term%'";
}

// Consulta para obtener los cursos, filtrados si hay un término de búsqueda
$consulta = "SELECT * FROM curso $where_sql ORDER BY ID_Curso ASC";
$resultado = mysqli_query($conexion, $consulta);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Cursos - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css"> <!-- Estilos generales -->
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_inscriptos.css"> <!-- Estilos para tablas -->
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_cursos.css"> 
</head>
<body class="fade-in">
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../../index.html">VALIDAR</a></li>
                    <!--<li> <a href="../../HTML/cursos.html">CURSOS</a> </li>-->
                    <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <!-- Contenido dinámico por JS -->
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <!-- Menú Off-canvas -->
    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav>
            <ul>
                <li><a href="../../index.html">VALIDAR</a></li>
                <!--<li> <a href="../../HTML/cursos.html">CURSOS</a> </li>-->
                <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            

            <div class="contenido-principal">

                <div id="header-container">
                    <h1 class="main-title">Gestión de Cursos</h1>
                    
                    <div class="header-buttons">
                        <a href="agregar_curso.php" class="menu-btn"><i class="fas fa-plus"></i> AGREGAR</a>
                        <a href="filtrar_cursos.php" class="menu-btn"><i class="fas fa-filter"></i> FILTRAR</a>
                        <a href="editar_duracion_cursos.php" class="menu-btn"><i class="fas fa-calendar-alt"></i> ACTUALIZAR FECHAS</a>
                        <a href="gestion_externos.php" class="menu-btn"><i class="fas fa-info-circle"></i> EXTERNOS</a>
                        <a href="descargar_encuesta_form.php" class="menu-btn"><i class="fas fa-download"></i> DESCARGAR ENCUESTA</a>
                    </div>

                </div>
                

                <div class="filters-container">
                    <form method="get" action="gestionar_cursos.php" class="filter-form">
                        <div class="filter-group">
                            <input type="text" name="search" id="search-main" placeholder="Buscar por nombre de curso..." value="<?= htmlspecialchars($search_term) ?>">
                        </div>
                        <div class="search-group">
                            <button type="submit" id="filter-btn" style="width: auto;"><i class="fas fa-search"></i> Buscar</button>
                            <a href="gestionar_cursos.php" id="reset-btn"><i class="fas fa-undo"></i> Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="results-container">
                    <table id="tabla-cursos">
                        <thead>
                            <tr>
                                <th>ID Curso</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                                <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($fila['ID_Curso']); ?></td>
                                        <td><?= htmlspecialchars($fila['Nombre_Curso']); ?></td>
                                        <td><?= htmlspecialchars($fila['Categoria']); ?></td>
                                        <td><?= htmlspecialchars($fila['Tipo']); ?></td>
                                        <td class="actions">
                                            <a href="editar_curso.php?id=<?= $fila['ID_Curso'] ?>" class="btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="confirmar_eliminar_curso.php?id=<?= $fila['ID_Curso'] ?>" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 2rem;">No se encontraron cursos que coincidan con la búsqueda.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
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
                <a href="#"><i class="fab fa-youtube"></i></a>
                <a href="#"><i class="fab fa-linkedin"></i></a>
            </div>
            <div class="footer-legal">
                <a href="#">Contacto</a><br>
                <a href="#">Políticas de Privacidad</a>
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
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Alumno'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <br>
                        <li><a href="../../PHP/ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="../../PHP/ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="../../PHP/ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="../../PHP/ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="#">Mi Perfil</a></li>
                        <br>
                        <li><a href="#">Inscripciones</a></li>
                        <br>
                        <li><a href="#">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="../../PHP/inicio_sesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>
<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
    <i class="fas fa-arrow-up"></i>
</a>

    <script src="../../JavaScript/general.js"></script>
    <script src="../../JavaScript/ADMIN/gestionar_cursos.js"></script>
    <script>
        fetch('../get_user_name.php')
            .then(response => response.json())
            .then(data => {
                const sessionControls = document.getElementById('session-controls');
                const mobileNav = document.querySelector('.off-canvas-menu nav ul');
                let sessionHTML = '';

                if (data.user_name) {
                    let dropdownMenu;
                    if (data.user_rol === 1) { // Admin
                        dropdownMenu = `
                            <button class="user-menu-toggle">Hola, ${data.user_name}. <i class="fas fa-chevron-down"></i></button>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                                    <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                                    <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                    <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                            <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    } else if (data.user_rol === 2) { // Alumno
                        // Redirigir si no es admin
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    // Redirigir si no está logueado
                    window.location.href = '../inicio_sesion.php?error=acceso_denegado';
                }

                // Añadir al menú móvil
                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>