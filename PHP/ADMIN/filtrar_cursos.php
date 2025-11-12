<?php
session_start();
include("../conexion.php");

// Obtener valores distintos para los filtros
$modalidades = mysqli_query($conexion, "SELECT DISTINCT Modalidad FROM curso ORDER BY Modalidad");
$categorias = mysqli_query($conexion, "SELECT DISTINCT Categoria FROM curso ORDER BY Categoria");
$comisiones = mysqli_query($conexion, "SELECT DISTINCT COALESCE(Comision, 'Unica') AS Comision FROM inscripcion ORDER BY Comision");
$tipos = mysqli_query($conexion, "SELECT DISTINCT Tipo FROM curso ORDER BY Tipo");

// Obtener los filtros enviados por GET
$filtro_general = isset($_GET['filtro_general']) ? mysqli_real_escape_string($conexion, $_GET['filtro_general']) : '';
$modalidad = isset($_GET['modalidad']) ? mysqli_real_escape_string($conexion, $_GET['modalidad']) : '';
$categoria = isset($_GET['categoria']) ? mysqli_real_escape_string($conexion, $_GET['categoria']) : '';
$comision = isset($_GET['comision']) ? mysqli_real_escape_string($conexion, $_GET['comision']) : '';
$tipo = isset($_GET['tipo']) ? mysqli_real_escape_string($conexion, $_GET['tipo']) : '';
$ver_sin_docente = isset($_GET['sin_docente']); // <-- nuevo botón

// Si se presiona el botón "Ver cursos sin docente"
if ($ver_sin_docente) {

    $consulta_sql = "SELECT * FROM curso WHERE Docente = '' ";
    $stmt = mysqli_prepare($conexion, $consulta_sql);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);

} else {
    // --- CONSULTA NORMAL CON FILTROS ---

    $where_clauses = [];
    $params = [];
    $types = '';

    // Filtro general
    if (!empty($filtro_general)) {
        $like_term = "%" . $filtro_general . "%";
        $where_clauses[] = "(Nombre_Curso LIKE ? OR Docente LIKE ? OR Modalidad LIKE ? OR Categoria LIKE ? OR Tipo LIKE ?)";
        for ($i = 0; $i < 5; $i++) {
            $params[] = &$like_term;
            $types .= 's';
        }
    }

    // Filtros específicos
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

    if (!empty($comision)) {
        if ($comision === 'Unica') {
            $where_clauses[] = "c.ID_Curso IN (SELECT DISTINCT ID_Curso FROM inscripcion WHERE Comision IS NULL)";
        } else {
            $where_clauses[] = "c.ID_Curso IN (SELECT DISTINCT ID_Curso FROM inscripcion WHERE Comision = ?)";
            $params[] = &$comision;
            $types .= 's';
        }
    }

    if (!empty($tipo)) {
        $where_clauses[] = "Tipo = ?";
        $params[] = &$tipo;
        $types .= 's';
    }

    // Construcción final de la consulta
    $consulta_sql = "SELECT c.* FROM curso c";
    if (!empty($where_clauses)) {
        $consulta_sql .= " WHERE " . implode(' AND ', $where_clauses);
    }
    $consulta_sql .= " ORDER BY ID_Curso";

    // Preparar y ejecutar
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
}

// Calcular la cantidad de cursos encontrados
$totalCursos = $resultado ? mysqli_num_rows($resultado) : 0;

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
                    <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
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
                <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            
            <div class="contenido-principal">
                
                <div id="header-container">
                    <h1 class="main-title">Filtrar Cursos</h1>
                    <a href="gestionar_cursos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
                </div>

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
                            <label for="comision">Comisión:</label>
                            <select name="comision" id="comision">
                                <option value="">Todas</option>
                                <?php while ($fila = mysqli_fetch_assoc($comisiones)): ?>
                                    <option value="<?= htmlspecialchars($fila['Comision']) ?>" <?= $comision == $fila['Comision'] ? 'selected' : '' ?>><?= htmlspecialchars($fila['Comision']) ?></option>
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

                            <button type="submit" name="sin_docente" value="1" id="null-btn" class="btn-null">
                                <i class="fas fa-user-slash"></i> Ver cursos sin docente
                            </button>

                        </div>
                    </form>
                </div>

                <div class="results-container">

                    <?php $totalCursos = $resultado ? mysqli_num_rows($resultado) : 0; ?>

                    <div class="info-resultados">
                        <?php if (isset($_GET['sin_docente'])): ?>
                            <p>Se encontraron <strong><?= $totalCursos ?></strong> cursos sin docente asignado.</p>
                        <?php elseif ($totalCursos > 0): ?>
                            <p>Se encontraron <strong><?= $totalCursos ?></strong> cursos con los filtros aplicados.</p>
                        <?php else: ?>
                            <p>No se encontraron cursos con los filtros seleccionados.</p>
                        <?php endif; ?>
                    </div>

                    <form action="confirmar_eliminar_curso.php" method="POST" id="form-eliminar-multiple">
                        <table id="tabla-cursos">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="seleccionar-todos" title="Seleccionar todos"></th>
                                    <th>ID Curso</th>
                                    <th>Nombre</th>
                                    <th>Docente</th>
                                    <th>Modalidad</th>
                                    <th>Categoría</th>
                                    <th>Carga Horaria</th>
                                    <th>Tipo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($resultado && mysqli_num_rows($resultado) > 0): ?>
                                    <?php while ($fila = mysqli_fetch_assoc($resultado)): ?>
                                        <tr>
                                            <td class="col-checkbox"><input type="checkbox" name="cursos_a_eliminar[]" value="<?= $fila['ID_Curso'] ?>" class="checkbox-curso"></td>
                                            <td><?= htmlspecialchars($fila['ID_Curso']); ?></td>
                                            <td><?= htmlspecialchars($fila['Nombre_Curso']); ?></td>
                                            <td><?= htmlspecialchars($fila['Docente']); ?></td>
                                            <td><?= htmlspecialchars($fila['Modalidad']); ?></td>
                                            <td><?= htmlspecialchars($fila['Categoria']); ?></td>
                                            <td><?= htmlspecialchars($fila['Carga_Horaria']); ?></td>
                                            <td><?= htmlspecialchars($fila['Tipo']); ?></td>
                                            <td class="actions">
                                                <a href="editar_curso.php?id=<?= $fila['ID_Curso'] ?>" class="btn-edit" title="Editar"><i class="fas fa-pencil-alt"></i></a>
                                                <a href="confirmar_eliminar_curso.php?id=<?= $fila['ID_Curso'] ?>" class="btn-delete" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" style="text-align: center; padding: 2rem;">No se encontraron cursos con los filtros aplicados.</td>
                                    </tr>
                                <?php endif; ?> 
                            </tbody>
                        </table>
                        <button type="submit" id="btn-eliminar-flotante" class="btn-flotante-eliminar" style="display:none;">
                            <i class="fas fa-trash-alt"></i> Eliminar Seleccionados (<span id="contador-seleccion">0</span>)
                        </button>
                    </form>
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
                <li><a href="<?php echo $base_path; ?>index.html">Validar</a></li>
                <li><a href="<?php echo $html_path; ?>sobre_nosotros.html">Sobre Nosotros</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-dynamic-nav">
            <?php if (isset($_SESSION['user_name'])): ?>
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Estudiante'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
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
                    <li><a href="<?php echo $php_path; ?>inicio_sesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
    </footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../../JavaScript/general.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnFlotante = document.getElementById('btn-eliminar-flotante');
            const contadorSpan = document.getElementById('contador-seleccion');
            const checkboxes = document.querySelectorAll('.checkbox-curso');
            const seleccionarTodos = document.getElementById('seleccionar-todos');

            function actualizarBotonFlotante() {
                const seleccionados = document.querySelectorAll('.checkbox-curso:checked').length;
                contadorSpan.textContent = seleccionados;
                if (seleccionados > 0) {
                    btnFlotante.style.display = 'flex';
                } else {
                    btnFlotante.style.display = 'none';
                }
            }

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', actualizarBotonFlotante);
            });

            seleccionarTodos.addEventListener('change', function() {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = this.checked;
                });
                actualizarBotonFlotante();
            });

            document.getElementById('form-eliminar-multiple').addEventListener('submit', function(e) {
                if (document.querySelectorAll('.checkbox-curso:checked').length === 0) {
                    alert('Debe seleccionar al menos un curso para eliminar.');
                    e.preventDefault();
                }
            });
        });
    </script>
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
                    } else {
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    window.location.href = '../inicio_sesion.php';
                }
                mobileNav.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>