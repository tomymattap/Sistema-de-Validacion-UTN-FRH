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
$consulta = "SELECT * FROM curso $where_sql ORDER BY ID_Curso";
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
    <link rel="stylesheet" href="../../CSS/verinscriptos.css"> <!-- Estilos para tablas -->
    <link rel="stylesheet" href="../../CSS/gestionar_cursos.css"> <!-- Estilos específicos de esta página -->
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
                    <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
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
                <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <aside class="menu-lateral">
                <a href="agregar_curso.php" class="menu-btn"><i class="fas fa-plus"></i> AGREGAR</a>
                <a href="filtrar_cursos.php" class="menu-btn"><i class="fas fa-filter"></i> FILTRAR</a>
                <button class="menu-btn"><i class="fas fa-file-csv"></i> SUBIR CSV</button>
            </aside>

            <div class="contenido-principal">
                <h1 class="main-title">Gestión de Cursos</h1>

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
                    <table id="results-table">
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
                                            <a href="editar_curso.php?id=<?= htmlspecialchars($fila['ID_Curso']) ?>" class="btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="confirmar_eliminar_curso.php?id=<?= htmlspecialchars($fila['ID_Curso']) ?>" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
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

<?php
include('../footer.php');
?>