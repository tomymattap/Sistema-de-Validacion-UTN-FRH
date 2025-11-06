<?php
session_start();
include("../conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Curso - Admin</title>
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
                <h1 class="main-title">Agregar Nuevo Curso</h1>
                <div class="form-container">
                    <form action="insertar_curso.php" method="POST" class="form-grid">
                        <div class="form-group">
                            <label for="nombre_curso">Nombre del Curso</label>
                            <input type="text" id="nombre_curso" name="nombre_curso" required>
                        </div>
                        <div class="form-group">
                            <label for="categoria">Categoría</label>
                            <input type="text" id="categoria" name="categoria" required>
                        </div>
                        <div class="form-group">
                            <label for="modalidad">Modalidad (opcional)</label>
                            <input type="text" id="modalidad" name="modalidad">
                        </div>
                        <div class="form-group">
                            <label for="docente">Docente (opcional)</label>
                            <input type="text" id="docente" name="docente">
                        </div>
                        <div class="form-group">
                            <label for="carga_horaria">Carga Horaria (opcional)</label>
                            <input type="text" id="carga_horaria" name="carga_horaria" placeholder="Ej: 40 horas">
                        </div>
                        <div class="form-group">
                            <label for="tipo">Tipo</label>
                            <select id="tipo" name="tipo" required>
                                <option value="GENUINO">Genuino</option>
                                <option value="CERTIFICACION">Certificación</option>
                            </select>
                        </div>
                        <div class="form-group full-width">
                            <label for="descripcion">Descripción (opcional)</label>
                            <textarea id="descripcion" name="descripcion"></textarea>
                        </div>
                        <div class="form-group full-width">
                            <label for="requisitos">Requisitos (opcional)</label>
                            <textarea id="requisitos" name="requisitos"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="reset" class="btn-reset"><i class="fas fa-undo"></i> Limpiar</button>
                            <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Curso</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <!-- Contenido del pie de página -->
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