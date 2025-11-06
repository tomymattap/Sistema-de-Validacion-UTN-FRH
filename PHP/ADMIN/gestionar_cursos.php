<?php
$page_title = 'Gestionar Cursos - Admin';
$extra_styles = ['verinscriptos.css', 'gestionar_cursos.css'];
include('../header.php');

// La validación de sesión ya está en el header
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    echo '<script>window.location.href = "' . htmlspecialchars($base_path) . 'PHP/iniciosesion.php?error=acceso_denegado";</script>';
    exit;
}

include("../conexion.php");

// Manejo de la búsqueda con sentencias preparadas para prevenir SQL Injection
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$consulta = "SELECT * FROM curso";
if (!empty($search_term)) {
    $consulta .= " WHERE ID_Curso LIKE ? OR Nombre_Curso LIKE ?";
    $stmt = $conexion->prepare($consulta);
    $search_param = "%" . $search_term . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = mysqli_query($conexion, $consulta);
}
?>
    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            

            <div class="contenido-principal">

                <div id="header-container">
                    <h1 class="main-title">Gestión de Cursos</h1>
                    
                    <div class="header-buttons">
                        <a href="agregar_curso.php" class="menu-btn"><i class="fas fa-plus"></i> AGREGAR</a>
                        <a href="filtrar_cursos.php" class="menu-btn"><i class="fas fa-filter"></i> FILTRAR</a>
                        <a href="filtrar_cursos.php" class="menu-btn"><i class="fas fa-file-csv"></i> COMPARTIR FORMULARIO</a>
                        
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