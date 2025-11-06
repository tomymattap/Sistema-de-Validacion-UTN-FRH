<?php
session_start();
include("../conexion.php");

// Obtener valores distintos para los filtros
$modalidades = mysqli_query($conexion, "SELECT DISTINCT Modalidad FROM curso ORDER BY Modalidad");
$categorias = mysqli_query($conexion, "SELECT DISTINCT Categoria FROM curso ORDER BY Categoria");
$tipos = mysqli_query($conexion, "SELECT DISTINCT Tipo FROM curso ORDER BY Tipo");

$filtro_general = isset($_GET['filtro_general']) ? mysqli_real_escape_string($conexion, $_GET['filtro_general']) : '';
$modalidad = isset($_GET['modalidad']) ? mysqli_real_escape_string($conexion, $_GET['modalidad']) : '';
$categoria = isset($_GET['categoria']) ? mysqli_real_escape_string($conexion, $_GET['categoria']) : '';
$tipo = isset($_GET['tipo']) ? mysqli_real_escape_string($conexion, $_GET['tipo']) : '';

$where_clauses = [];
$params = [];
$types = '';

if (!empty($filtro_general)) {
    $like_term = "%" . $filtro_general . "%";
    $where_clauses[] = "(Nombre_Curso LIKE ? OR Docente LIKE ? OR Modalidad LIKE ? OR Categoria LIKE ? OR Tipo LIKE ?)";
    for ($i = 0; $i < 5; $i++) {
        $params[] = &$like_term;
        $types .= 's';
    }
}

if (!empty($modalidad)) {
    $where_clauses[] = "Modalidad = ?";
    $params[] = &$modalidad;
    $types .= 's';
}

if (!empty($categoria)) {
    $where_clauses[] = "Categoria = ?";
    $params[] = &$categoria;
    $types .= 's';
}

if (!empty($tipo)) {
    $where_clauses[] = "Tipo = ?";
    $params[] = &$tipo;
    $types .= 's';
}

$consulta_sql = "SELECT * FROM curso";
if (!empty($where_clauses)) {
    $consulta_sql .= " WHERE " . implode(' AND ', $where_clauses);
}
$consulta_sql .= " ORDER BY ID_Curso";

$stmt = mysqli_prepare($conexion, $consulta_sql);

if ($stmt) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
} else {
    $resultado = false;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Filtrar Cursos - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/verinscriptos.css"> 
    <link rel="stylesheet" href="../../CSS/gestionar_cursos.css"> 
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
                    <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls"></div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </header>

    <div class="off-canvas-menu" id="off-canvas-menu">
        <button class="close-btn" aria-label="Cerrar menú">&times;</button>
        <nav>
            <ul>
                <li><a href="../../index.html">VALIDAR</a></li>
                <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <aside class="menu-lateral">
                <a href="gestionar_cursos.php" class="menu-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
            </aside>

            <div class="contenido-principal">
                <h1 class="main-title">Filtrar Cursos</h1>

                <div class="filters-container filter-courses-container">
                    <form method="get" action="filtrar_cursos.php" class="filter-form">
                        <div class="filter-group">
                            <label for="filtro_general">Búsqueda general:</label>
                            <input type="text" name="filtro_general" id="filtro_general" placeholder="Nombre, docente, etc." value="<?= htmlspecialchars($filtro_general) ?>">
                        </div>
                        <div class="filter-group">
                            <label for="modalidad">Modalidad:</label>
                            <select name="modalidad" id="modalidad">
                                <option value="">Todas</option>
                                <?php while ($fila = mysqli_fetch_assoc($modalidades)): ?>
                                    <option value="<?= htmlspecialchars($fila['Modalidad']) ?>" <?= $modalidad == $fila['Modalidad'] ? 'selected' : '' ?>><?= htmlspecialchars($fila['Modalidad']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="categoria">Categoría:</label>
                            <select name="categoria" id="categoria">
                                <option value="">Todas</option>
                                <?php while ($fila = mysqli_fetch_assoc($categorias)): ?>
                                    <option value="<?= htmlspecialchars($fila['Categoria']) ?>" <?= $categoria == $fila['Categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($fila['Categoria']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="tipo">Tipo:</label>
                            <select name="tipo" id="tipo">
                                <option value="">Todos</option>
                                <?php while ($fila = mysqli_fetch_assoc($tipos)): ?>
                                    <option value="<?= htmlspecialchars($fila['Tipo']) ?>" <?= $tipo == $fila['Tipo'] ? 'selected' : '' ?>><?= htmlspecialchars($fila['Tipo']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="search-group">
                            <button type="submit" id="filter-btn" style="width: auto;"><i class="fas fa-filter"></i> Filtrar</button>
                            <a href="filtrar_cursos.php" id="reset-btn"><i class="fas fa-undo"></i> Limpiar</a>
                        </div>
                    </form>
                </div>

                <div class="results-container">
                    <table id="tabla-cursos">
                        <thead>
                            <tr>
                                <th>ID Curso</th>
                                <th>Nombre</th>
                                <th>Docente</th>
                                <th>Modalidad</th>
                                <th>Categoría</th>
                                <th>Carga Horaria</th>
                                <th>Descripción</th>
                                <th>Requisitos</th>
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
                                        <td><?= htmlspecialchars($fila['Docente']); ?></td>
                                        <td><?= htmlspecialchars($fila['Modalidad']); ?></td>
                                        <td><?= htmlspecialchars($fila['Categoria']); ?></td>
                                        <td><?= htmlspecialchars($fila['Carga_Horaria']); ?></td>
                                        <td><?= htmlspecialchars($fila['Descripcion']); ?></td>
                                        <td class="col-descripcion"><?= htmlspecialchars($fila['Requisitos']); ?></td>
                                        <td><?= htmlspecialchars($fila['Tipo']); ?></td>
                                        <td class="actions">
                                            <a href="editar_curso.php?id=<?= $fila['ID_Curso'] ?>" class="btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                            <a href="confirmar_eliminar_curso.php?id=<?= $fila['ID_Curso'] ?>" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" style="text-align: center; padding: 2rem;">No se encontraron cursos con los filtros aplicados.</td>
                                </tr>
                            <?php endif; ?> 
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer"></footer>

    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
        <i class="fas fa-arrow-up"></i>
    </a>

    <script src="../../JavaScript/general.js"></script>
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
                                    <li><a href="verinscriptos.php">Ver Inscriptos</a></li>
                                    <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                                    <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>`;
                        sessionHTML = `
                            <li><a href="verinscriptos.php">Ver Inscriptos</a></li>
                            <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="../logout.php">Cerrar Sesión</a></li>`;
                    } else {
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    window.location.href = '../../HTML/iniciosesion.html';
                }
                mobileNav.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>