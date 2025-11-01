<?php
include("../conexion.php");

// Manejo de filtros
$filter_nombre = isset($_GET['nombre']) ? mysqli_real_escape_string($conexion, $_GET['nombre']) : '';
$filter_curso = isset($_GET['curso']) ? intval($_GET['curso']) : 0;
$filter_estado = isset($_GET['estado']) ? mysqli_real_escape_string($conexion, $_GET['estado']) : '';

// Query para listar inscriptos con JOIN a alumno y curso
$where = [];
if ($filter_nombre !== '') {
    $where[] = "(a.Nombre_Alumno LIKE '%$filter_nombre%' OR a.Apellido_Alumno LIKE '%$filter_nombre%')";
}
if ($filter_curso > 0) {
    $where[] = "i.ID_Curso = $filter_curso";
}
if ($filter_estado !== '') {
    $where[] = "i.Estado_Cursada = '$filter_estado'";
}
$where_sql = '';
if (count($where) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT i.ID_Inscripcion, a.ID_Cuil_Alumno, a.Nombre_Alumno, a.Apellido_Alumno, c.Nombre_Curso, i.Cuatrimestre, i.Anio, i.Estado_Cursada
        FROM inscripcion i
        JOIN alumno a ON i.ID_Cuil_Alumno = a.ID_Cuil_Alumno
        JOIN curso c ON i.ID_Curso = c.ID_Curso
        $where_sql
        ORDER BY i.Anio DESC, i.ID_Inscripcion DESC";

$res = mysqli_query($conexion, $sql);
if (!$res) {
    die('Error en la consulta: ' . mysqli_error($conexion));
}

// Obtener lista de cursos y alumnos para los selects
$cursos_res = mysqli_query($conexion, "SELECT ID_Curso, Nombre_Curso FROM curso ORDER BY Nombre_Curso");
$cursos = [];
while ($row = mysqli_fetch_assoc($cursos_res)) { $cursos[] = $row; }

$alumnos_res = mysqli_query($conexion, "SELECT ID_Cuil_Alumno, Nombre_Alumno, Apellido_Alumno FROM alumno ORDER BY Apellido_Alumno");
$alumnos = [];
while ($row = mysqli_fetch_assoc($alumnos_res)) { $alumnos[] = $row; }


$estados = ['En curso', 'Finalizado', 'CERTIFICADA', 'ASISTIDO'];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inscriptos - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/verinscriptos.css">
    <style>
        .filter-form { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 1rem; }
        .add-form-container { background-color: #f8f9fa; padding: 2rem; border-radius: 8px; margin-bottom: 2rem; }
        .add-form { display: flex; flex-wrap: wrap; gap: 1rem; }
        .add-form > * { flex: 1 1 180px; }
        .add-form .btn-add { flex-grow: 0; background-color: var(--color-secundario-2); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .add-form .btn-add:hover { background-color: #7ab831; }
        .sub-title { margin-bottom: 1rem; font-size: 1.5rem; }
        .actions { display: flex; gap: 0.5rem; }
        .action-form { margin: 0; }
        .btn-edit, .btn-delete { background: none; border: none; cursor: pointer; font-size: 1.1rem; padding: 0.5rem; border-radius: 50%; transition: background-color 0.3s; }
        .btn-edit { color: var(--color-secundario-3); }
        .btn-edit:hover { background-color: #fff3d9; }
        .btn-delete { color: var(--color-secundario-4); }
        .btn-delete:hover { background-color: #ffe0e5; }
        .status-badge { padding: 0.3em 0.6em; border-radius: 12px; font-weight: 700; font-size: 0.8rem; color: white; }
        .status-en-curso { background-color: #17a2b8; }
        .status-finalizado { background-color: #28a745; }
        .status-certificada { background-color: var(--color-secundario-5); }
        .status-asistido { background-color: #6c757d; }
        .no-results { text-align: center; padding: 2rem; }
        #filter-btn, #reset-btn { display: inline-flex; align-items: center; gap: 0.5rem; }
    </style>
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
            <div class="session-controls" id="session-controls">
                <button class="user-menu-toggle">Hola, Admin. <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <ul>
                        <li><a href="verinscriptos.php">Ver Inscriptos</a></li>
                        <li><a href="../../HTML/gestionarcursos.html">Gestionar Cursos</a></li>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <li><a href="#">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>
        </div>
        <div class="mobile-nav">
            <button class="close-menu" aria-label="Cerrar menú">&times;</button>
            <nav>
                <ul>
                    <li><a href="../../index.html">INICIO</a></li>
                    <li><a href="../../HTML/sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
                <div class="mobile-session-controls" id="mobile-session-controls"></div>
            </nav>
        </div>
    </header>

<main>
<section class="admin-section">
<div class="admin-container">
    <h1 class="main-title">Gestión de Inscriptos</h1>

    <div class="filters-container">
        <form method="get" class="filter-form">
            <div class="filter-group">
                <input type="text" name="nombre" id="search-main" placeholder="Buscar por nombre o apellido..." value="<?= htmlspecialchars($filter_nombre) ?>">
                <select name="curso">
                    <option value="0">-- Todos los cursos --</option>
                    <?php foreach($cursos as $curso): ?>
                        <option value="<?= $curso['ID_Curso'] ?>" <?= ($filter_curso == $curso['ID_Curso']) ? 'selected' : '' ?>><?= htmlspecialchars($curso['Nombre_Curso']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="estado">
                    <option value="">-- Todos los estados --</option>
                    <?php foreach($estados as $est): ?>
                        <option value="<?= $est ?>" <?= ($filter_estado === $est) ? 'selected' : '' ?>><?= $est ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="search-group">
                <button type="submit" id="filter-btn"><i class="fas fa-search"></i> Filtrar</button>
                <a href="verinscriptos.php" id="reset-btn"><i class="fas fa-undo"></i> Limpiar</a>
            </div>
        </form>
    </div>

    <div class="add-form-container">
        <h2 class="sub-title">Agregar Nueva Inscripción</h2>
        <form method="post" action="insertar_inscripto.php" class="add-form">
            <select name="ID_Cuil_Alumno" required>
                <option value="">-- Seleccionar alumno --</option>
                <?php foreach ($alumnos as $al): ?>
                    <option value="<?= $al['ID_Cuil_Alumno'] ?>"><?= htmlspecialchars($al['Apellido_Alumno'].', '.$al['Nombre_Alumno']).' ('. $al['ID_Cuil_Alumno'] .')' ?></option>
                <?php endforeach; ?>
            </select>
            <select name="ID_Curso" required>
                <option value="">-- Seleccionar curso --</option>
                <?php foreach($cursos as $curso): ?>
                    <option value="<?= $curso['ID_Curso'] ?>"><?= htmlspecialchars($curso['Nombre_Curso']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="Cuatrimestre" required>
                <option value="Primer Cuatrimestre">Primer Cuatrimestre</option>
                <option value="Segundo Cuatrimestre">Segundo Cuatrimestre</option>
                <option value="Anual">Anual</option>
            </select>
            <input type="number" name="Anio" value="<?= date('Y') ?>" required placeholder="Año">
            <select name="Estado_Cursada" required>
                 <option value="">-- Estado de cursada --</option>
                <?php foreach($estados as $est): ?>
                    <option value="<?= $est ?>"><?= $est ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-add"><i class="fas fa-plus"></i> Agregar</button>
        </form>
    </div>

    <div class="results-container">
        <table id="results-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Alumno</th>
                    <th>CUIL</th>
                    <th>Curso</th>
                    <th>Periodo</th>
                    <th>Año</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($res) > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($res)): ?>
                    <tr>
                        <td><?= $r['ID_Inscripcion'] ?></td>
                        <td><?= htmlspecialchars($r['Apellido_Alumno'].', '.$r['Nombre_Alumno']) ?></td>
                        <td><?= $r['ID_Cuil_Alumno'] ?></td>
                        <td><?= htmlspecialchars($r['Nombre_Curso']) ?></td>
                        <td><?= htmlspecialchars($r['Cuatrimestre']) ?></td>
                        <td><?= $r['Anio'] ?></td>
                        <td><span class="status-badge status-<?= strtolower(str_replace(' ', '-', $r['Estado_Cursada'])) ?>"><?= htmlspecialchars($r['Estado_Cursada']) ?></span></td>
                        <td class="actions">
                            <form method="post" action="editar_inscripto.php" class="action-form"> 
                                <button type="submit" name="ID_Inscripcion" value="<?= $r['ID_Inscripcion'] ?>" class="btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></button>
                            </form>
                            <form method="post" action="eliminar_inscripto.php" class="action-form" onsubmit="return confirm('¿Está seguro de que desea eliminar la inscripción #<?= $r['ID_Inscripcion'] ?>?');">
                                <input type="hidden" name="ID_Inscripcion" value="<?= $r['ID_Inscripcion'] ?>">
                                <button type="submit" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="no-results">No se encontraron inscripciones que coincidan con los filtros.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</section>
</main>

<footer class="site-footer">
        <!-- Footer content -->
</footer>

    <script src="../../JavaScript/general.js"></script>
    <script src="../../JavaScript/verinscriptos.js"></script>
    <a href="#" class="scroll-to-top-btn" title="Volver arriba"><i class="fas fa-arrow-up"></i></a>
</body>
</html>
