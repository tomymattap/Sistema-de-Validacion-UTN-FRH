<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
    exit;
}

// Redirigir si no hay datos de alumnos en la sesión
if (empty($_SESSION['cert_data'])) {
    // Si se envían datos por POST, los guardamos en la sesión.
    // Esto ocurre cuando se viene de tabla_alumnos_certif.php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['alumnos'])) {
        $_SESSION['cert_data'] = $_POST;
    } else {
        // Si no hay datos POST ni en sesión, se redirige al inicio del proceso.
        header("Location: seleccionar_alum_certif.php");
        exit();
    }
}

$cert_data = $_SESSION['cert_data'];

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
$current_page = 'seleccionar_alum_certif.php'; // Se mantiene para marcar el menú
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivos para Certificado</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>dropzone.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>gestionar_cursos.css"> <!-- Reutilizamos algunos estilos de formularios -->
    <link rel="stylesheet" href="<?php echo $css_path; ?>subir_archivos.css"> <!-- Nuevos estilos -->
    <script src="../../JavaScript/dropzone-min.js"></script>
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
                <li><a href="<?php echo $html_path; ?>sobrenosotros.html">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
            </ul>
        </nav>
        <div class="session-controls hide-on-mobile">
            <div class="user-menu-container">
                <a href="#" class="btn-sesion user-menu-toggle">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                <div class="dropdown-menu">
                    <ul>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php" class="active">Emitir Certificados</a></li>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php">Gestionar Administradores</a></li>
                        <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
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
            <li><a href="<?php echo $html_path; ?>sobrenosotros.html">SOBRE NOSOTROS</a></li>
            <li><a href="<?php echo $html_path; ?>contacto.html">CONTACTO</a></li>
            <li id="mobile-session-section">
                <a href="#" class="user-menu-toggle-mobile">Hola, <?php echo htmlspecialchars($_SESSION['user_name']); ?> <i class="fas fa-chevron-down"></i></a>
                <ul class="submenu">
                    <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                    <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                    <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php" class="active">Emitir Certificados</a></li>
                    <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php">Gestionar Administradores</a></li>
                    <li><a href="<?php echo $php_path; ?>logout.php">Cerrar Sesión</a></li>
                </ul>
            </li>
        </ul>
    </nav>
</div>

<main class="admin-section" style="padding-top: 4rem; padding-bottom: 4rem;">
        <div class="admin-container">
            <h1 class="main-title">Archivos para el Certificado</h1>
            <p style="text-align: center; margin-bottom: 2rem;">Sube las firmas y logos necesarios para generar los certificados.</p>

            <form action="generar_certificado.php" method="post" id="upload-form">
                
                <!-- Campos ocultos para mantener los datos de los alumnos -->
                <?php
                foreach ($cert_data as $key => $value) {
                    if (is_array($value)) {
                        foreach ($value as $cuil => $data) {
                            // Asegurarse de que el alumno fue seleccionado
                            if (isset($data['cuil'])) {
                                echo "<input type='hidden' name='alumnos[{$cuil}][cuil]' value='" . htmlspecialchars($data['cuil']) . "'>";
                                echo "<input type='hidden' name='alumnos[{$cuil}][estado]' value='" . htmlspecialchars($data['estado']) . "'>";
                            }
                        }
                    } else {
                        echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
                    }
                }
                ?>
                
                <input type="hidden" name="firma_secretario_path" id="firma_secretario_path">
                <input type="hidden" name="firma_docente_director_path" id="firma_docente_director_path">
                <input type="hidden" name="logo_camara_path" id="logo_camara_path">

                <div class="form-group">
                    <label for="firma_secretario">1. Firma del Secretario (PNG, obligatoria)</label>
                    <div id="dz-firma-secretario" class="dropzone"></div>
                </div>

                <div class="form-group">
                    <label>2. Firma Docente/Director (PNG, obligatoria)</label>
                    <div class="checkbox-container">
                        <input type="checkbox" id="es_director" name="es_director" value="1">
                        <label for="es_director">Marcar si es firma de un Director (para certificados externos)</label>
                    </div>
                    <div id="dz-firma-docente-director" class="dropzone"></div>
                </div>

                <div class="form-group">
                    <label for="logo_camara">3. Logo de la Cámara Asociada (PNG, opcional)</label>
                    <div id="dz-logo-camara" class="dropzone"></div>
                </div>

                <div class="form-group">
                    <label for="nombre_instituto">4. Nombre completo del Instituto (si aplica)</label>
                    <input type="text" id="nombre_instituto" name="nombre_instituto" class="form-control">
                </div>

                <div class="form-buttons">
                    <button type="submit" class="submit-btn">Generar Certificados</button>
                    <div id="error-message-container" class="institutional-error-message" style="display: none; margin-left: 1rem; flex: 1;">
                        <i class="fas fa-exclamation-triangle"></i>
                        <span id="error-message-text"></span>
                    </div>
                </div>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Desactivar el autodescubrimiento de Dropzone
        Dropzone.autoDiscover = false;

        const errorMessageContainer = document.getElementById('error-message-container');
        const errorMessageText = document.getElementById('error-message-text');

        // Function to display institutional error messages
        const displayInstitutionalError = (message) => {
            errorMessageText.textContent = message;
            errorMessageContainer.style.display = 'flex'; // Use flex to align icon and text
            // Hide after a few seconds
            setTimeout(() => {
                errorMessageContainer.style.display = 'none';
            }, 5000);
        };

        // Configuración común para todos los Dropzones
        const commonConfig = {
            maxFilesize: 2, // MB
            maxFiles: 1,
            acceptedFiles: "image/png",
            addRemoveLinks: true,
            dictDefaultMessage: "<span class='dz-button'>Haz clic</span> o arrastra una imagen PNG aquí",
            dictRemoveFile: "Quitar archivo",
            dictMaxFilesExceeded: "No puedes subir más de un archivo."
        };

        // Dropzone para Firma del Secretario
        const dzFirmaSecretario = new Dropzone("#dz-firma-secretario", {
            ...commonConfig,
            url: "upload_handler.php",
            paramName: "firma_secretario",
            success: function(file, response) {
                const res = JSON.parse(response); // Parse the JSON response
                if(res.status === 'success') {
                    document.getElementById('firma_secretario_path').value = res.filename;
                } else {
                    displayInstitutionalError('Error al subir la firma del secretario: ' + res.message);
                    this.removeFile(file);
                }
            },
            removedfile: function(file) {
                document.getElementById('firma_secretario_path').value = "";
                file.previewElement.remove();
            }
        });

        // Dropzone para Firma del Docente/Director
        const dzFirmaDocenteDirector = new Dropzone("#dz-firma-docente-director", {
            ...commonConfig,
            url: "upload_handler.php",
            paramName: "firma_docente_director",
            success: function(file, response) {
                const res = JSON.parse(response); // Parse the JSON response
                if(res.status === 'success') {
                    document.getElementById('firma_docente_director_path').value = res.filename;
                } else {
                    displayInstitutionalError('Error al subir la firma del docente/director: ' + res.message);
                    this.removeFile(file);
                }
            },
            removedfile: function(file) {
                document.getElementById('firma_docente_director_path').value = "";
                file.previewElement.remove();
            }
        });

        // Dropzone para Logo de la Cámara
        const dzLogoCamara = new Dropzone("#dz-logo-camara", {
            ...commonConfig,
            url: "upload_handler.php",
            paramName: "logo_camara",
            success: function(file, response) {
                const res = JSON.parse(response); // Parse the JSON response
                if(res.status === 'success') {
                    document.getElementById('logo_camara_path').value = res.filename;
                } else {
                    displayInstitutionalError('Error al subir el logo: ' + res.message);
                    this.removeFile(file);
                }
            },
            removedfile: function(file) {
                document.getElementById('logo_camara_path').value = "";
                file.previewElement.remove();
            }
        });

        // Validación del formulario antes de enviar
        document.getElementById("upload-form").addEventListener("submit", function(e) {
            if (document.getElementById('firma_secretario_path').value === '') {
                displayInstitutionalError('Debe subir la firma del secretario.');
                e.preventDefault();
            }
            if (document.getElementById('firma_docente_director_path').value === '') {
                displayInstitutionalError('Debe subir la firma del docente o director.');
                e.preventDefault();
            }
        });

        // Add Cancel button functionality
        const formButtons = document.querySelector('.form-buttons');
        const submitButton = formButtons.querySelector('.submit-btn'); // Get the existing submit button

        const cancelButton = document.createElement('button');
        cancelButton.type = 'button';
        cancelButton.className = 'btn-cancelar-cert'; // Changed class name
        cancelButton.textContent = 'Cancelar';
        cancelButton.addEventListener('click', () => {
            window.history.back();
        });
        
        // Insert the cancel button before the submit button
        formButtons.insertBefore(cancelButton, submitButton);
    });
    </script>
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
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionarinscriptos.php">Gestionar Inscriptos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionar_cursos.php">Gestionar Cursos</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <br>
                        <li><a href="<?php echo $php_path; ?>ADMIN/gestionaradmin.php">Gestionar Administradores</a></li>
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
                    <li><a href="<?php echo $php_path; ?>iniciosesion.php">Iniciar Sesión</a></li>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</footer>
<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>
</body>
</html>