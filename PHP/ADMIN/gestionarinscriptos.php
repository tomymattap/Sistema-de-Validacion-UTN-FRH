<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
    exit;
}

// **BLOQUE DE SEGURIDAD: Forzar cambio de contraseña**
if (isset($_SESSION['force_password_change'])) {
    header('Location: cambiar_contrasena_obligatorio.php');
    exit;
}

include("../conexion.php");

// --- Lógica para obtener datos para los filtros ---
$cursos_res = mysqli_query($conexion, "SELECT ID_Curso, Nombre_Curso FROM curso ORDER BY Nombre_Curso");
$cursos = [];
while ($row = mysqli_fetch_assoc($cursos_res)) { $cursos[] = $row; }

$anios_res = mysqli_query($conexion, "SELECT DISTINCT Anio FROM inscripcion ORDER BY Anio DESC");
$anios = [];
while ($row = mysqli_fetch_assoc($anios_res)) { $anios[] = $row['Anio']; }

$estados = ['Pendiente', 'En Curso', 'Finalizada', 'Certificada'];
$cuatrimestres = ['Primer Cuatrimestre', 'Segundo Cuatrimestre', 'Anual'];

// --- Definición de rutas para el header/footer temporal ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
$current_page = 'gestionarinscriptos.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Inscriptos - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>gestionarinscriptos.css">
</head>
<body>

<!-- ======================= HEADER ========================= -->
<header class="site-header">
    <div class="header-container">
        <div class="logo">
            <a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a>
        </div>
        <nav class="main-nav hide-on-mobile">
            <ul>
                <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
                <li><a href="<?php echo $html_path; ?>sobrenosotros.php">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.php">CONTACTO</a></li>
            </ul>
        </nav>
        <div class="session-controls hide-on-mobile">
            <?php if (isset($_SESSION['user_name'])):
                $user_rol = $_SESSION['user_rol'];
            ?>
                <div class="user-menu-container">
                    <a href="#" class="btn-sesion user-menu-toggle">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                    <div class="dropdown-menu">
                        <ul>
                            <?php if ($user_rol == 1): // Admin ?>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php" class="active">Gestionar Inscriptos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php">Gestionar Administradores</a></li>
                            <?php else: // Alumno ?>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $php_path; ?>iniciosesion.php" class="btn-sesion">INICIAR SESIÓN</a>
            <?php endif; ?>
        </div>
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
            <li><a href="<?php echo $base_path; ?>index.html">VALIDAR</a></li>
            <li><a href="<?php echo $html_path; ?>sobrenosotros.php">SOBRE NOSOTROS</a></li>
            <li><a href="<?php echo $html_path; ?>contacto.php">CONTACTO</a></li>
            <li id="mobile-session-section">
                <?php if (isset($_SESSION['user_name'])):
                    $user_rol = $_SESSION['user_rol'];
                ?>
                    <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="submenu">
                        <?php if ($user_rol == 1): // Admin ?>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php" class="active">Gestionar Inscriptos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php">Gestionar Administradores</a></li>
                        <?php else: // Alumno ?>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li> 
                            <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                    </ul>
                <?php else: ?>
                    <a href="<?php echo $php_path; ?>iniciosesion.php">INICIAR SESIÓN</a>
                <?php endif; ?>
            </li>
        </ul>
    </nav>
</div>

<!-- ===================== FIN HEADER ======================= -->

<main class="admin-section">
    <div class="admin-container">
        <h1 class="main-title">Gestionar Inscriptos</h1>

        <div class="tabs-container">
            <button class="tab active" data-tab="ver">Gestionar Inscriptos</button>
            <button class="tab" data-tab="agregar">Agregar Inscriptos</button>
        </div>

        <!-- Pestaña: Ver Inscriptos -->
        <div id="ver" class="tab-content active">
            <div class="filtros-box">
                <div class="filtro-row">
                    <input type="search" id="buscadorLive" placeholder="Buscar por Nombre, CUIL, Curso...">
                    <select id="filtroCurso">
                        <option value="">Seleccionar curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo $curso['ID_Curso']; ?>"><?php echo htmlspecialchars($curso['Nombre_Curso']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select id="filtroEstado">
                        <option value="">Estado de la cursada</option>
                        <?php foreach ($estados as $estado): ?><option value="<?php echo $estado; ?>"><?php echo $estado; ?></option><?php endforeach; ?>
                    </select>
                    <select id="filtroAnio">
                        <option value="">Año</option>
                        <?php foreach ($anios as $anio): ?><option value="<?php echo $anio; ?>"><?php echo $anio; ?></option><?php endforeach; ?>
                    </select>
                    <select id="filtroCuatrimestre">
                        <option value="">Cuatrimestre</option>
                        <?php foreach ($cuatrimestres as $cuatri): ?><option value="<?php echo $cuatri; ?>"><?php echo $cuatri; ?></option><?php endforeach; ?>
                    </select>
                </div>
                <div class="botones-filtros">
                    <button id="btnFiltrar" class="btn filtrar"><i class="fas fa-search"></i> Filtrar</button>
                    <button id="btnMostrarTodos" class="btn mostrar-todos">Mostrar Listado Completo</button>
                    <button id="btnLimpiar" class="btn limpiar">&#x21BB; Limpiar Filtros</button>
                </div>
            </div>
            <div id="resultados" class="tabla-inscriptos"></div>
        </div>

        <!-- Pestaña: Agregar Inscriptos -->
        <div id="agregar" class="tab-content">
            <div class="add-container">
                <nav class="tabs-secondary">
                    <button data-tab="manual" class="active">Carga Manual</button>
                    <button data-tab="archivo">Cargar con Archivo</button>
                </nav>
                <div id="tab-manual" class="tab-panel-secondary active">
                    <h2>Carga Manual de Inscripción</h2>
                    <!-- Formulario de carga manual irá aquí -->
                </div>
                <div id="tab-archivo" class="tab-panel-secondary">
                    <h2>Carga Masiva con Archivo CSV</h2>
                    <!-- Formulario de carga de archivos irá aquí -->
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ======================= FOOTER ========================= -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-logo-info">
            <img src="<?php echo $img_path; ?>UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
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
                <li><a href="<?php echo $html_path; ?>sobrenosotros.html">Sobre Nosotros</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">Contacto</a></li>
            </ul>
        </div>
        <div class="footer-separator"></div>
        <div class="footer-dynamic-nav">
            <?php if (isset($_SESSION['user_name'])): ?>
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Alumno'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                    <?php else: ?>
                        <li><a href="#">Mi Perfil</a></li>
                        <li><a href="#">Inscripciones</a></li>
                        <li><a href="#">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="<?php echo $php_path; ?>iniciosesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>
<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

<!-- ===================== FIN FOOTER ======================= -->

<script src="<?php echo $js_path; ?>general.js"></script>
<script src="<?php echo $js_path; ?>gestionarinscriptos.js"></script>
<script>
// Script para manejar las pestañas principales
document.querySelectorAll('.tabs-container .tab').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tabs-container .tab').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.getElementById(tab.dataset.tab).classList.add('active');
    });
});

// Script para manejar las pestañas secundarias (dentro de Agregar)
document.querySelectorAll('.tabs-secondary button').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tabs-secondary button').forEach(t => t.classList.remove('active'));
        tab.classList.add('active');
        document.querySelectorAll('.tab-panel-secondary').forEach(c => c.style.display = 'none');
        document.querySelector(`#${tab.dataset.tab}`).style.display = 'block';
    });
});
</script>

</body>
</html>