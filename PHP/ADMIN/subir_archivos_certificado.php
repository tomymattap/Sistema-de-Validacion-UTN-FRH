<?php
session_start();

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivos para Certificado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/dropzone.css">
    <link rel="stylesheet" href="../../CSS/gestionar_cursos.css"> <!-- Reutilizamos algunos estilos de formularios -->
    <link rel="stylesheet" href="../../CSS/subir_archivos.css"> <!-- Nuevos estilos -->
    <script src="../../JavaScript/dropzone-min.js"></script>
</head>
<body>
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
        </div>
    </header>

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
                </div>
            </form>
        </div>
    </main>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Desactivar el autodescubrimiento de Dropzone
        Dropzone.autoDiscover = false;

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
                if(response.status === 'success') {
                    document.getElementById('firma_secretario_path').value = response.filename;
                } else {
                    alert('Error al subir la firma del secretario: ' + response.message);
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
                if(response.status === 'success') {
                    document.getElementById('firma_docente_director_path').value = response.filename;
                } else {
                    alert('Error al subir la firma del docente/director: ' + response.message);
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
                if(response.status === 'success') {
                    document.getElementById('logo_camara_path').value = response.filename;
                } else {
                    alert('Error al subir el logo: ' + response.message);
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
                alert('Error: Debes subir la firma del secretario.');
                e.preventDefault();
            }
            if (document.getElementById('firma_docente_director_path').value === '') {
                alert('Error: Debes subir la firma del docente o director.');
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>