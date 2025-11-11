<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../inicio_sesion.php?error=acceso_denegado');
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
$html_path = $base_path . 'HTML/'; // Ruta correcta a la carpeta HTML
$php_path = $base_path . 'PHP/';
$current_page = 'gestionar_inscriptos.php';
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
    <link rel="stylesheet" href="<?php echo $css_path; ?>gestionar_inscriptos.css">
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
                <li><a href="<?php echo $html_path; ?>sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
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
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php" class="active">Gestionar Inscriptos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                                <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                            <?php else: // Estudiante ?>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li>
                                <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                            <?php endif; ?>
                            <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo $php_path; ?>inicio_sesion.php" class="btn-sesion">INICIAR SESIÓN</a>
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
            <li><a href="<?php echo $html_path; ?>sobre_nosotros.html">SOBRE NOSOTROS</a></li>
            <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
            <li id="mobile-session-section">
                <?php if (isset($_SESSION['user_name'])):
                    $user_rol = $_SESSION['user_rol'];
                ?>
                    <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                    <ul class="submenu">
                        <?php if ($user_rol == 1): // Admin ?>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_inscriptos.php" class="active">Gestionar Inscriptos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                            <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_admin.php">Gestionar Administradores</a></li>
                        <?php else: // Estudiante ?>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/perfil.php">Mi Perfil</a></li>
                            <li><a href="<?php echo $php_path; ?>ALUMNO/inscripciones.php">Inscripciones</a></li> 
                            <li><a href="<?php echo $php_path; ?>ALUMNO/certificaciones.php">Certificaciones</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                    </ul>
                <?php else: ?>
                    <a href="<?php echo $php_path; ?>inicio_sesion.php">INICIAR SESIÓN</a>
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
            <button class="tab active" data-tab="ver">Ver Inscriptos</button>
            <button class="tab" data-tab="agregar">Agregar Inscriptos</button>
        </div>

        <!-- ========== VER INSCRIPTOS ========== -->
        <div id="ver" class="tab-content active">
            <div class="filtros-box">
                <div class="filtro-row">
                    <input type="search" id="buscadorLive" placeholder="Buscar por Nombre, CUIL, Curso...">
                    <select id="filtroCurso">
                        <option value="">Curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo $curso['ID_Curso']; ?>"><?php echo htmlspecialchars($curso['Nombre_Curso']); ?></option>
                        <?php endforeach; ?>
                    </select>

                    <select id="filtroComision" disabled>
                        <option value="">Comisión</option>
                    </select>

                    <select id="filtroEstado">
                        <option value="">Estado</option>
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
                    <button id="btnLimpiar" class="btn limpiar">&#x21BB; Limpiar</button>
                </div>
            </div>

            <div id="resultados" class="tabla-inscriptos"></div>
        </div>

        <!-- ========== AGREGAR INSCRIPTOS ========== -->
        <div id="agregar" class="tab-content">
            <div class="add-container">
                <nav class="tabs-secondary">
                    <button data-tab="manual" class="active">Carga Manual</button>
                    <button data-tab="archivo">Carga con Archivo</button>
                </nav>

                <!-- ======== CARGA MANUAL ======== -->
                <div id="tab-manual" class="tab-panel-secondary active">
                    <div class="multistep-form-container">
                        <!-- Barra de progreso -->
                        <div class="progress-bar-container">
                            <div class="progress-bar">
                                <div class="progress-step active" data-step="1">
                                    <div class="step-label">Paso 1</div>
                                    <div class="step-title">Registrar Estudiante</div>
                                </div>
                                <div class="progress-step" data-step="2">
                                    <div class="step-label">Paso 2</div>
                                    <div class="step-title">Registrar Inscripción</div>
                                </div>
                            </div>
                        </div>

                        <!-- Mensaje de éxito -->
                        <div id="inscripcion-exitosa-mensaje" class="mensaje-exito" style="display: none;">
                            <i class="fas fa-check-circle"></i>
                            <span>¡Inscripción realizada con éxito!</span>
                        </div>

                        <!-- Paso 1: Datos del Alumno -->
                        <div id="step-1" class="form-step active">
                            <h2 class="step-main-title">Paso 1: Registrar Estudiante</h2>
                            <form id="form-step-1" class="form-carga-pasos">
                                <div class="form-grid">
                                    <div class="campo-form"><label for="cuil">CUIL del estudiante *</label><input type="text" id="cuil" name="ID_Cuil_Alumno" required pattern="[0-9]{10,11}" title="Solo números (11 dígitos sin guiones)"></div>
                                    <div class="campo-form"><label for="dni">DNI del estudiante *</label><input type="text" id="dni" name="DNI_Alumno" required pattern="[0-9]{7,8}" title="Solo números (7 u 8 dígitos)"></div>
                                    <div class="campo-form"><label for="nombre">Nombre *</label><input type="text" id="nombre" name="Nombre_Alumno" required></div>
                                    <div class="campo-form"><label for="apellido">Apellido *</label><input type="text" id="apellido" name="Apellido_Alumno" required></div>
                                    <div class="campo-form"><label for="email">Email *</label><input type="email" id="email" name="Email_Alumno" required></div>
                                    <div class="campo-form"><label for="direccion">Dirección</label><input type="text" id="direccion" name="Direccion"></div>
                                    <div class="campo-form"><label for="telefono">Teléfono</label><input type="tel" id="telefono" name="Telefono"></div>
                                </div>
                                <div id="mensaje-step-1" class="mensaje-form" style="display:none;"></div>
                                <div class="botones-form-pasos">
                                    <button type="button" class="btn-cancelar-paso">Cancelar</button>
                                    <button type="submit" class="btn-continuar">Registrar y Continuar</button>
                                </div>
                            </form>
                        </div>

                        <!-- Paso 2: Datos de la Inscripción -->
                        <div id="step-2" class="form-step">
                            <h2 class="step-main-title">Paso 2: Registrar Inscripción</h2>
                            <form id="form-step-2" class="form-carga-pasos">
                                <div class="form-grid">
                                    <div class="campo-form">
                                        <label for="curso-insc">Curso a inscribir *</label>
                                        <select id="curso-insc" name="ID_Curso" required>
                                            <option value="">Seleccione un curso</option>
                                            <?php foreach ($cursos as $curso): ?>
                                                <option value="<?php echo $curso['ID_Curso']; ?>"><?php echo htmlspecialchars($curso['Nombre_Curso']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="campo-form">
                                        <label for="comision-insc">Comisión *</label>
                                        <input type="text" id="comision-insc" name="Comision" placeholder="Ej: A, B o 1" required>
                                    </div>
                                    <div class="campo-form">
                                        <label for="cuatrimestre-insc">Cuatrimestre *</label>
                                        <select id="cuatrimestre-insc" name="Cuatrimestre" required>
                                            <option value="Primer Cuatrimestre">Primer Cuatrimestre</option>
                                            <option value="Segundo Cuatrimestre">Segundo Cuatrimestre</option>
                                            <option value="Anual">Anual</option>
                                        </select>
                                    </div>
                                    <div class="campo-form">
                                        <label for="anio-insc">Año *</label>
                                        <input type="number" id="anio-insc" name="Anio" value="<?php echo date('Y'); ?>" required min="2000" max="2100">
                                    </div>
                                    <div class="campo-form">
                                        <label for="estado-cursada-insc">Estado de la Cursada</label>
                                        <select id="estado-cursada-insc" name="Estado_Cursada" required>
                                            <option value="Pendiente">Pendiente</option>
                                            <option value="En Curso">En Curso</option>
                                            <option value="Finalizada">Finalizada</option>
                                            <option value="Certificada">Certificada</option>
                                        </select>
                                    </div>
                                </div>
                                <div id="mensaje-step-2" class="mensaje-form" style="display:none;"></div>
                                <div class="botones-form-pasos">
                                    <button type="button" class="btn-cancelar-paso">Cancelar</button>
                                    <button type="submit" class="btn-finalizar">Finalizar Inscripción</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

                <!-- ======== CARGA CON ARCHIVO ======== -->
                <div id="tab-archivo" class="tab-panel-secondary">
                    <section class="carga-archivo-container">
                        <h2 class="main-title">Cargar Inscriptos desde Archivo CSV</h2>

                        <div class="info-bar">
                            <p class="info-text">Revisá el formato del archivo antes de subirlo.</p>
                            <button type="button" class="info-btn" id="btnGuiaArchivo">¿Cómo subo un archivo?</button>
                        </div>

                        <form id="upload-form" method="POST" enctype="multipart/form-data" action="insertar_inscriptos_csv.php">
                            <div class="form-group">
                                <div class="dropzone" id="dropzone">
                                    <input type="file" id="archivo" name="archivo" accept=".csv" hidden>
                                    <p class="dz-message">Arrastrá el archivo aquí o hacé clic para seleccionarlo</p>
                                </div>
                            </div>

                            <div id="preview-container" class="preview-container" style="display:none;">
                                <h3>Vista previa del archivo (primeras 5 filas)</h3>
                                <table id="preview-table" class="preview-table"></table>
                                <div id="preview-errors" style="color:#b30000; font-weight:600; margin-top:8px;"></div>
                            </div>

                            <div class="form-buttons">
                                <button type="button" id="btnUploadConfirm" class="submit-btn">Subir Archivo</button>
                                <button type="button" class="btn-cancelar-cert" id="btnCancelarArchivo">Cancelar</button>
                            </div>
                            <div id="mensajeArchivo" style="margin-top:12px; font-weight:600;"></div>
                        </form>

                        <!-- Overlay con la guía para subir archivos -->
                        <div id="overlayGuia" class="overlay" aria-hidden="true">
                            <div class="overlay-content" role="dialog" aria-modal="true" aria-labelledby="tituloGuia">
                                <button class="close-btn" id="cerrarGuia" title="Cerrar">&times;</button>
                                <h3 id="tituloGuia">Guía para subir archivos de inscriptos</h3>
                                <p>Tu archivo debe ser .CSV (UTF-8) y contener las columnas en este orden exacto:</p>
                                <ul>
                                    <li><strong>CUIL</strong> (sin guiones)</li>
                                    <li><strong>DNI</strong></li>
                                    <li><strong>Apellido</strong></li>
                                    <li><strong>Nombre</strong></li>
                                    <li><strong>Email</strong></li>
                                    <li><strong>Dirección</strong></li>
                                    <li><strong>Teléfono</strong></li>
                                    <li><strong>Curso</strong> (nombre o ID_Curso, preferible ID_Curso)</li>
                                    <li><strong>Cuatrimestre</strong></li>
                                    <li><strong>Año</strong></li>
                                    <li><strong>Comisión</strong></li>
                                </ul>
                                <p><strong>Ejemplo de fila:</strong></p>
                                <p><code>20431223444,43122344,Fernández,Mateo,mateo@mail.com,Calle Falsa 123,1123456789,23,Primer Cuatrimestre,2025,A</code></p>
                                <div style="text-align:right; margin-top:1rem;">
                                    <button id="btnCerrarGuia" class="btn-cancelar-cert">Cerrar</button>
                                </div>
                            </div>
                        </div>

                    </section>
                </div>
            </div>
        </div>
    </div>
</main>


<!-- Modal para edición -->
<div id="edit-modal" class="edit-modal" style="display:none;">
    <div class="edit-modal-content">
        <button id="close-edit-modal" class="close-modal-btn" title="Cerrar">&times;</button>
        <div id="edit-modal-body"></div>
    </div>
</div>

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

<!-- ===================== FIN FOOTER ======================= -->

<script src="<?php echo $js_path; ?>general.js"></script>
<script src="<?php echo $js_path; ?>gestionar_inscriptos.js"></script>
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

// Script para manejar las pestañas secundarias (dentro de "Agregar Inscriptos")
document.querySelectorAll('.tabs-secondary button').forEach(btn => {
    btn.addEventListener('click', () => {
        // quitar la clase active de todos los botones
        document.querySelectorAll('.tabs-secondary button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');

        // ocultar todos los paneles
        document.querySelectorAll('.tab-panel-secondary').forEach(panel => panel.classList.remove('active'));

        // mostrar el panel correspondiente
        const tabId = 'tab-' + btn.dataset.tab; // ej: "tab-manual" o "tab-archivo"
        const panelToShow = document.getElementById(tabId);
        if (panelToShow) panelToShow.classList.add('active');
    });
});
</script>

</body>
</html>