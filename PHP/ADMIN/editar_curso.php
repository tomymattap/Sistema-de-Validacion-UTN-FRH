<?php
session_start();
include("../conexion.php");

$curso = null;
$id_curso = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Proceso de actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_curso'])) {
    $id_curso_post = filter_input(INPUT_POST, 'id_curso', FILTER_VALIDATE_INT);
    $nombre_curso = $_POST['nombre_curso'] ?? '';
    $modalidad = $_POST['modalidad'] ?? null;
    $docente = $_POST['docente'] ?? null;
    $carga_horaria = $_POST['carga_horaria'] ?? null;
    $descripcion = $_POST['descripcion'] ?? null;
    $requisitos = $_POST['requisitos'] ?? null;
    $categoria = $_POST['categoria'] ?? '';
    $tipo = $_POST['tipo'] ?? '';

    if ($id_curso_post && !empty($nombre_curso) && !empty($categoria) && !empty($tipo)) {
        $sql = "UPDATE curso SET Nombre_Curso=?, Modalidad=?, Docente=?, Carga_Horaria=?, Descripcion=?, Requisitos=?, Categoria=?, Tipo=? WHERE ID_Curso=?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssssssssi", $nombre_curso, $modalidad, $docente, $carga_horaria, $descripcion, $requisitos, $categoria, $tipo, $id_curso_post);
        
        if (mysqli_stmt_execute($stmt)) {
            header('Location: gestionar_cursos.php?status=updated');
            exit;
        } else {
            die('Error al actualizar el curso: ' . mysqli_stmt_error($stmt));
        }
    } else {
        die('Error: Faltan datos obligatorios.');
    }
}

// Cargar datos del curso para editar
if ($id_curso) {
    $sql = "SELECT * FROM curso WHERE ID_Curso = ?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_curso);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $curso = mysqli_fetch_assoc($resultado);
    } else {
        die('Curso no encontrado.');
    }
} else {
    die('ID de curso no válido.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Curso - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/verinscriptos.css">
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
            <div class="session-controls" id="session-controls">
                <!-- Contenido dinámico por JS -->
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span><span></span><span></span>
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
                <h1 class="main-title">Editar Curso <?= htmlspecialchars($curso['Nombre_Curso']) ?></h1>
                <div class="form-container">
                    <form action="editar_curso.php" method="POST" class="form-grid">
                        <input type="hidden" name="id_curso" value="<?= htmlspecialchars($curso['ID_Curso']) ?>">
                        
                        <div class="form-group">
                            <label for="nombre_curso">Nombre del Curso</label>
                            <input type="text" id="nombre_curso" name="nombre_curso" value="<?= htmlspecialchars($curso['Nombre_Curso']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categoría</label>
                            <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($curso['Categoria']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="modalidad">Modalidad (opcional)</label>
                            <input type="text" id="modalidad" name="modalidad" value="<?= htmlspecialchars($curso['Modalidad']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="docente">Docente (opcional)</label>
                            <input type="text" id="docente" name="docente" value="<?= htmlspecialchars($curso['Docente']) ?>">
                        </div>
                        <div class="form-group">
                            <label for="carga_horaria">Carga Horaria (opcional)</label>
                            <input type="text" id="carga_horaria" name="carga_horaria" value="<?= htmlspecialchars($curso['Carga_Horaria']) ?>" placeholder="Ej: 40 horas">
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="GENUINO" <?= $curso['Tipo'] == 'GENUINO' ? 'selected' : '' ?>>Genuino</option>
                                <option value="CERTIFICACION" <?= $curso['Tipo'] == 'CERTIFICACION' ? 'selected' : '' ?>>Certificación</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción (opcional)</label>
                            <textarea id="descripcion" name="descripcion"><?= htmlspecialchars($curso['Descripcion']) ?></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="requisitos">Requisitos (opcional)</label>
                            <textarea id="requisitos" name="requisitos"><?= htmlspecialchars($curso['Requisitos']) ?></textarea>
                        </div>
                        <div class="form-actions">
                            <a href="gestionar_cursos.php" class="btn-cancel"><i class="fas fa-times"></i> Cancelar</a>
                            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <!-- ... tu pie de página ... -->
    </footer>

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
                    } else {
                        window.location.href = '../../index.html';
                    }
                    sessionControls.innerHTML = dropdownMenu;
                } else {
                    window.location.href = '../iniciosesion.php';
                }

                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });
    </script>
</body>
</html>