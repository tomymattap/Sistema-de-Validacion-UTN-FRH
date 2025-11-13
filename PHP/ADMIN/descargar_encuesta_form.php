<?php
session_start();
include("../conexion.php");

// --- Security check for admin ---
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    // Not an admin, redirect to login
    header("Location: ../inicio_sesion.php?error=acceso_denegado");
    exit;
}

// --- Fetch courses for the dropdown ---
$cursos_query = "SELECT ID_Curso, Nombre_Curso FROM curso ORDER BY Nombre_Curso";
$cursos_result = mysqli_query($conexion, $cursos_query);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Descargar Encuestas - Admin</title>
    <link rel="icon" href="../../Imagenes/icon.png" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_cursos.css">
    <style>
        /* Estilos para mejorar el despliegue del select */
        #id_curso[size] {
            position: absolute; /* Permite que flote sobre el contenido */
            top: 100%; /* Se posiciona justo debajo del contenedor del label y el select original */
            left: 0;
            right: 0;
            z-index: 10; /* Asegura que esté por encima de otros elementos */
            border: 1px solid var(--color-secundario-1); /* Borde con color de acento */
            border-top: none; /* Quitamos el borde superior para que parezca una continuación */
            border-radius: 0 0 8px 8px; /* Bordes redondeados solo abajo */
            box-shadow: 0 5px 10px rgba(0,0,0,0.15); /* Sombra para dar profundidad */
            background-color: white;
        }
        #id_curso[size] option {
            padding: 10px; /* Más espacio interno para cada opción */
        }
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
                    <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <!-- Dynamic content by JS -->
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
                <li><a href="../../HTML/sobre_nosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="../../HTML/contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
    </div>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            
            <div class="contenido-principal">

                <div id="header-container">
                    <h1 class="main-title">Descargar Encuestas de Satisfacción</h1>
                    <a href="gestionar_cursos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
                </div>

                
                <div class="form-container">
                    <form action="descargar_encuesta_csv.php" method="POST" class="form-grid">
                        <!-- Campo de búsqueda manual -->
                        <div class="form-group full-width">
                            <label for="buscador_curso">Buscar Curso por Nombre</label>
                            <input type="text" id="buscador_curso" placeholder="Escriba para filtrar..." style="width: 100%;">
                        </div>

                        <!-- Contenedor del menú desplegable con posicionamiento relativo para el despliegue absoluto -->
                        <div class="form-group full-width" style="position: relative;">
                             <label for="id_curso">Seleccione un Curso</label>
                            <select id="id_curso" name="id_curso" required>
                                <option value="">-- Seleccionar Curso --</option>
                                <?php
                                if ($cursos_result && mysqli_num_rows($cursos_result) > 0) {
                                    while ($curso = mysqli_fetch_assoc($cursos_result)) {
                                        echo '<option value="' . htmlspecialchars($curso['ID_Curso']) . '">' . htmlspecialchars($curso['Nombre_Curso']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Nuevo desplegable para Comisiones -->
                        <div class="form-group full-width">
                            <label for="comision">Seleccione una Comisión</label>
                            <select id="comision" name="comision" required disabled>
                                <option value="">-- Primero seleccione un curso --</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-submit"><i class="fas fa-download"></i> Descargar CSV</button>
                        </div>
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
                    <a href="https://www.youtube.com/@facultadregionalhaedo-utn3647" target="_blank"><i class="fab fa-youtube"></i></a>
                    <a href="https://www.linkedin.com/school/utn-facultad-regional-haedo/" target="_blank"><i class="fab fa-linkedin"></i></a>
                </div>
                <div class="footer-legal">
                    <a href="mailto:extension@frh.utn.edu.ar">Contacto</a>
                    <br> 
                    <a href="#politicas">Políticas de Privacidad</a>
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
                <h4><?php echo $_SESSION['user_rol'] == 1 ? 'Admin' : 'Estudiante'; ?></h4>
                <ul>
                    <?php if ($_SESSION['user_rol'] == 1): ?>
                        <br>
                        <li><a href="gestionar_inscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="gestionar_admin.php">Gestionar Administradores</a></li>
                    <?php else: ?>
                        <br>
                        <li><a href="../ALUMNO/perfil.php">Mi Perfil</a></li>
                        <br>
                        <li><a href="../ALUMNO/inscripciones.php">Inscripciones</a></li>
                        <br>
                        <li><a href="../ALUMNO/certificaciones.php">Certificaciones</a></li>
                    <?php endif; ?>
                </ul>
            <?php else: ?>
                <h4>Acceso</h4>
                <ul>
                    <li><a href="../inicio_sesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

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

                const mobileMenuUl = document.querySelector('.off-canvas-menu nav ul');
                mobileMenuUl.insertAdjacentHTML('beforeend', sessionHTML);
            });

        // --- Lógica para el buscador y el desplegable de comisiones ---
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscador_curso');
            const selectCursos = document.getElementById('id_curso');
            const selectComision = document.getElementById('comision');
            const opcionesOriginales = Array.from(selectCursos.options);

            // Función para cargar comisiones dinámicamente
            const cargarComisiones = async (cursoId) => {
                selectComision.innerHTML = '<option value="">Cargando...</option>';
                selectComision.disabled = true;

                if (!cursoId) {
                    selectComision.innerHTML = '<option value="">-- Primero seleccione un curso --</option>';
                    return;
                }

                try {
                    const response = await fetch(`../API/get_comisiones.php?curso_id=${cursoId}`);
                    if (!response.ok) throw new Error('Error al cargar comisiones');
                    
                    const comisiones = await response.json();
                    selectComision.innerHTML = ''; // Limpiar

                    if (comisiones.length > 0) {
                        selectComision.disabled = false;
                        selectComision.add(new Option('Todas las comisiones', 'TODAS'));
                        
                        comisiones.forEach(com => {
                            const option = new Option(com.Comision, com.Comision);
                            selectComision.add(option);
                        });

                        if (comisiones.some(c => c.Comision === 'A')) {
                            selectComision.value = 'A';
                        }
                    } else {
                        selectComision.innerHTML = '<option value="">No hay comisiones para este curso</option>';
                    }
                } catch (error) {
                    console.error(error);
                    selectComision.innerHTML = '<option value="">Error al cargar</option>';
                }
            };

            // --- Lógica para el buscador de cursos que filtra el select ---
            const normalizarTexto = (texto) => {
                return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            };

            buscador.addEventListener('input', function() {
                const textoBusquedaNormalizado = normalizarTexto(this.value);
                
                selectCursos.innerHTML = '';

                const opcionesFiltradas = opcionesOriginales.filter(opcion => {
                    if (opcion.value === "") return true;
                    return normalizarTexto(opcion.text).includes(textoBusquedaNormalizado);
                });

                if (this.value.length > 0) {
                    selectCursos.size = Math.max(2, Math.min(opcionesFiltradas.length, 6));
                } else {
                    selectCursos.size = 1;
                }

                if (opcionesFiltradas.length > 1) {
                    opcionesFiltradas.forEach(opcion => {
                        selectCursos.add(opcion.cloneNode(true));
                    });
                } else {
                    const opcionNoResultados = document.createElement('option');
                    opcionNoResultados.value = "";
                    opcionNoResultados.textContent = "No se encontraron cursos";
                    opcionNoResultados.disabled = true;
                    selectCursos.add(opcionNoResultados);
                }
            });

            // Al hacer clic, se actualiza el valor y se contrae la lista.
            // Esto disparará el evento 'change'.
            selectCursos.addEventListener('click', function(e) {
                if (e.target.tagName === 'OPTION') {
                    this.value = e.target.value;
                }
                if (this.size > 1) {
                    this.size = 1;
                }
            });

            // El evento 'change' es el responsable final de cargar las comisiones.
            selectCursos.addEventListener('change', function() {
                cargarComisiones(this.value);
            });
        });
    </script>
</body>
</html>