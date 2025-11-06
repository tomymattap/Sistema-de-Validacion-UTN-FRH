<?php
$page_title = 'Gestionar Inscriptos - Admin';
$extra_styles = ['gestionarinscriptos.css']; // Cargar CSS específico
include('../header.php');

// La validación de sesión ya está en el header, pero podemos ser más específicos
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    // Si no es admin, redirigir a la página de inicio o de login
    echo '<script>window.location.href = "' . $base_path . 'PHP/iniciosesion.php?error=acceso_denegado";</script>';
    exit;
}

include("../conexion.php"); // La conexión a la BD

// La lógica para definir $php_path, $css_path, etc., ahora está en header.php


// --- Lógica para obtener datos para los filtros ---
$cursos_res = mysqli_query($conexion, "SELECT ID_Curso, Nombre_Curso FROM curso ORDER BY Nombre_Curso");
$cursos = [];
while ($row = mysqli_fetch_assoc($cursos_res)) { $cursos[] = $row; }

$anios_res = mysqli_query($conexion, "SELECT DISTINCT Anio FROM inscripcion ORDER BY Anio DESC");
$anios = [];
while ($row = mysqli_fetch_assoc($anios_res)) { $anios[] = $row['Anio']; }

$estados = ['Pendiente', 'En Curso', 'Finalizada', 'Certificada'];
$cuatrimestres = ['Primer Cuatrimestre', 'Segundo Cuatrimestre', 'Anual'];

?>

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

<?php
$extra_scripts = ['gestionarinscriptos.js']; // Cargar JS específico
include('../footer.php');
?>
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