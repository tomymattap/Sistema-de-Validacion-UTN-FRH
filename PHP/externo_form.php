<?php
include 'conexion.php';
$token = $_GET['token'] ?? '';
$error_message = '';
$valido = false;

if (empty($token)) {
    $error_message = "No se proporcionó un token de acceso.";
} else {
    $sql = "SELECT * FROM acceso_externo 
            WHERE token = ? 
              AND valido_hasta > NOW()
              AND usado = 0";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);
    mysqli_stmt_execute($stmt);
    $resultado = mysqli_stmt_get_result($stmt);
    $valido = mysqli_num_rows($resultado) > 0;

    if (!$valido) {
        $error_message = "El enlace utilizado es inválido, ha expirado o ya ha sido utilizado.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Nuevo Curso de Certificación Externa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/ADMIN/gestionar_cursos.css">    
    
</head>
<body class="fade-in">
    <header class="site-header">
        <div class="header-container">
            <div class="logo"><a href="../index.html"><img src="../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a></div>
        </div>
    </header>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <div class="contenido-principal">
                <div id="header-container">
                    <h1 class="main-title">Registrar Nuevo Curso de Certificación</h1>
                </div>

                <?php if (!$valido): ?>
                    <div class="confirmacion-container" style="border-left-color: var(--color-secundario-4); text-align: center;">
                        <div class="confirmacion-header" style="justify-content: center;">
                            <i class="fas fa-times-circle" style="color: var(--color-secundario-4);"></i>
                            <h3>Enlace no válido</h3>
                        </div>
                        <p><?= htmlspecialchars($error_message) ?></p>
                        <a href="../index.html" class="menu-btn volver-btn" style="margin-top: 1rem;"><i class="fas fa-home"></i> Volver al Inicio</a>
                    </div>
                <?php else: ?>
                    <div class="form-container">
                        <form action="guardar_curso_externo.php" method="POST" class="form-grid" enctype="multipart/form-data">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                            
                            <div class="form-group">
                                <label for="nombre">Nombre del Curso</label>
                                <input type="text" id="nombre" name="nombre" required>
                            </div>
                            <div class="form-group">
                                <label for="categoria">Categoría</label>
                                <input type="text" id="categoria" name="categoria" placeholder="Ej: Aeronáutico, Ferrocarril, etc." required>
                            </div>
                            <div class="form-group">
                                <label for="modalidad">Modalidad</label>
                                <input type="text" id="modalidad" name="modalidad" required>
                            </div>
                            <div class="form-group">
                                <label for="docente">Docente (opcional)</label>
                                <input type="text" id="docente" name="docente">
                            </div>
                            <div class="form-group full-width">
                                <label for="carga">Carga Horaria (en horas)</label>
                                <input type="number" id="carga" name="carga" required>
                            </div>
                            <div class="form-group full-width">
                                <label for="descripcion">Descripción</label>
                                <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                            </div>
                            <div class="form-group full-width">
                                <label for="requisitos">Requisitos</label>
                                <textarea id="requisitos" name="requisitos" rows="3"></textarea>
                            </div>

                            <div class="form-group">
                                <label for="institucion1">Institución 1</label>
                                <input type="text" id="institucion1" name="institucion1" required>
                            </div>
                            <div class="form-group">
                                <label for="institucion2">Institución 2 (Opcional)</label>
                                <input type="text" id="institucion2" name="institucion2">
                            </div>
                            <div class="form-group">
                                <label for="institucion3">Institución 3 (Opcional)</label>
                                <input type="text" id="institucion3" name="institucion3">
                            </div>
                            <div class="form-group">
                                <label for="archivo_evaluacion">Archivo con datos necesarios de la Certificación (Opcional, solo PDF)</label>
                                <div class="file-upload-zone" id="fileUploadZone">
                                    <p>Haz clic para seleccionar un archivo PDF de tu máquina.</p>
                                    <input type="file" id="archivo_evaluacion" name="archivo_evaluacion" accept="application/pdf" style="display: none;">
                                </div>
                                <div id="fileDisplay" style="margin-top: 10px; display: none;">
                                    <span id="fileName"></span> <button type="button" id="removeFileBtn" class="btn-cancelar-cert"><i class="fas fa-times"></i> Eliminar</button>
                                </div>
                            </div>
                            
                                <input type="hidden" name="tipo" value="Certificación">

                            <div class="form-actions">
                                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Guardar Curso</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer"></footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../JavaScript/general.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const fileUploadZone = document.getElementById('fileUploadZone');
            const fileInput = document.getElementById('archivo_evaluacion');
            const fileDisplay = document.getElementById('fileDisplay');
            const fileNameSpan = document.getElementById('fileName');
            const removeFileBtn = document.getElementById('removeFileBtn');

            // Manejar clic en la zona de subida
            fileUploadZone.addEventListener('click', () => fileInput.click());

            // Manejar selección de archivo (ya sea por clic o arrastre)
            fileInput.addEventListener('change', (e) => handleFiles(e.target.files));

            function handleFiles(files) {
                if (files.length > 1) {
                    alert('Solo se permite subir un archivo.');
                    fileInput.value = ''; // Limpiar el input
                    return;
                }
                const file = files[0];
                if (file && file.type === 'application/pdf') {
                    fileNameSpan.textContent = file.name;
                    fileDisplay.style.display = 'block';
                } else {
                    alert('Solo se permiten archivos PDF.');
                    fileInput.value = ''; // Limpiar el input
                    fileDisplay.style.display = 'none';
                }
            }

            // Eliminar archivo seleccionado
            removeFileBtn.addEventListener('click', () => {
                fileInput.value = ''; // Limpiar el input de tipo file
                fileDisplay.style.display = 'none';
                fileNameSpan.textContent = '';
            });
        });
    </script>
</body>
</html>
