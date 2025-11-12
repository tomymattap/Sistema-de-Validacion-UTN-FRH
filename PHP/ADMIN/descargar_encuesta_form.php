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
                        <div class="form-actions">
                            <button type="submit" class="btn-submit"><i class="fas fa-download"></i> Descargar CSV</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <!-- Footer content -->
    </footer>

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

        // --- Lógica para el buscador de cursos que filtra el select ---
        document.addEventListener('DOMContentLoaded', function() {
            const buscador = document.getElementById('buscador_curso');
            const selectCursos = document.getElementById('id_curso');
            const opcionesOriginales = Array.from(selectCursos.options);

            // Función para normalizar texto (quitar tildes y a minúsculas)
            const normalizarTexto = (texto) => {
                return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
            };

            buscador.addEventListener('input', function() {
                const textoBusquedaNormalizado = normalizarTexto(this.value);
                
                // Limpiar el select actual
                selectCursos.innerHTML = '';

                // Filtrar y añadir opciones que coincidan
                const opcionesFiltradas = opcionesOriginales.filter(opcion => {
                    // Siempre incluir la opción por defecto
                    if (opcion.value === "") return true;
                    // Comparar textos normalizados
                    return normalizarTexto(opcion.text).includes(textoBusquedaNormalizado);
                });

                // Si hay texto en el buscador, expandir el select para mostrar resultados
                if (this.value.length > 0) {
                    // Mostrar hasta 5 resultados, o menos si hay menos (mínimo 2 para que se vea como lista)
                    selectCursos.size = Math.max(2, Math.min(opcionesFiltradas.length, 6));
                } else {
                    // Si el buscador está vacío, volver al tamaño normal
                    selectCursos.size = 1;
                }

                // Si hay más de una opción (la de "Seleccionar Curso" + al menos un resultado)
                if (opcionesFiltradas.length > 1) {
                    opcionesFiltradas.forEach(opcion => {
                        // Clonar la opción para no moverla del array original
                        selectCursos.add(opcion.cloneNode(true));
                    });
                } else {
                    // Si solo queda la opción por defecto, mostrar mensaje de no resultados
                    const opcionNoResultados = document.createElement('option');
                    opcionNoResultados.value = "";
                    opcionNoResultados.textContent = "No se encontraron cursos";
                    opcionNoResultados.disabled = true;
                    selectCursos.add(opcionNoResultados);
                }
            });

            // Opcional: Al hacer clic en una opción, contraer el menú
            selectCursos.addEventListener('click', function() {
                if (this.size > 1) {
                    this.size = 1;
                }
            });
        });
    </script>
</body>
</html>