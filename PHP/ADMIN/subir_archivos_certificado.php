<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar rol de administrador
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
    exit;
}

// Guardar datos de alumnos de la tabla anterior en la sesión si vienen por POST
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['alumnos'])) {
    $_SESSION['cert_data_alumnos'] = $_POST;
}

// Si no hay datos de alumnos en la sesión, redirigir al inicio del proceso
if (empty($_SESSION['cert_data_alumnos'])) {
    header("Location: seleccionar_alum_certif.php");
    exit();
}

$error_messages = [];
$temp_dir_name = 'temp_certificados';
$upload_dir = __DIR__ . '/cert_uploads/' . $temp_dir_name . '/';

// Procesamiento del formulario de subida de archivos
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_files'])) {

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $tipo_certificado = $_POST['tipo_certificado'] ?? '';
    $es_director = isset($_POST['es_director']);

    $cert_data_for_pdf = [
        'tipo_certificado' => $tipo_certificado,
        'tipo_actividad' => $_POST['tipo_actividad'] ?? '',
        'camara_organizadora' => $_POST['camara_organizadora'] ?? '',
        'institutos_codictantes' => $_POST['institutos_codictantes'] ?? '',
        'es_director' => $es_director,
        'files' => []
    ];

    // --- Validación de campos de texto ---
    if (empty($tipo_certificado)) { $error_messages[] = "Debe seleccionar el Tipo de Certificado."; }
    if (empty($cert_data_for_pdf['tipo_actividad'])) { $error_messages[] = "El campo 'Tipo de actividad' es obligatorio."; }

// --- Manejo de archivos subidos ---
    $file_fields = [
        'firma_secretario' => 'Firma del secretario',
        'logo_institucional' => 'Logo institucional',
        'firma_docente' => 'Firma del docente', // Genuino
        'firma_autoridad' => 'Firma del Docente/Director' // Externo
    ];
    
    $uploaded_files = [];
    foreach ($file_fields as $field_name => $label) {
        if (isset($_FILES[$field_name]) && $_FILES[$field_name]['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES[$field_name]['tmp_name'];
            $file_name = uniqid($field_name . '_', true) . '.' . pathinfo($_FILES[$field_name]['name'], PATHINFO_EXTENSION);
            $dest_path = $upload_dir . $file_name;

            if (move_uploaded_file($file_tmp_path, $dest_path)) {
                $uploaded_files[$field_name] = $dest_path;
            } else {
                $error_messages[] = "Error al mover el archivo: {$label}.";
            }
        }
    }

    // --- Validación de archivos según tipo de certificado ---
    if (empty($uploaded_files['firma_secretario'])) { $error_messages[] = "La firma del secretario es obligatoria."; }
    if (empty($uploaded_files['logo_institucional'])) { $error_messages[] = "El logo institucional es obligatorio."; }

    if ($tipo_certificado === 'genuino') {
        if (empty($uploaded_files['firma_docente'])) {
            $error_messages[] = "La firma del docente es obligatoria para certificados genuinos.";
        }
    } elseif ($tipo_certificado === 'externo') {
        if (empty($uploaded_files['firma_autoridad'])) {
            $error_messages[] = "La firma del docente/director es obligatoria para certificados externos.";
        } else {
            // Renombrar la clave para que coincida con la lógica de generación de PDF
            if ($es_director) {
                $uploaded_files['firma_director'] = $uploaded_files['firma_autoridad'];
            } else {
                $uploaded_files['firma_docente'] = $uploaded_files['firma_autoridad'];
            }
            unset($uploaded_files['firma_autoridad']);
        }
        if (empty($cert_data_for_pdf['camara_organizadora'])) {
            $error_messages[] = "El campo 'Nombre de la cámara organizadora' es obligatorio para certificados externos.";
        }
        if (empty($cert_data_for_pdf['institutos_codictantes'])) {
            $error_messages[] = "El campo 'Nombre instituto/s codictante/s' es obligatorio para certificados externos.";
        }
    }

    if (empty($error_messages)) {
        $cert_data_for_pdf['files'] = $uploaded_files;
        $_SESSION['cert_data_for_pdf'] = $cert_data_for_pdf;

        // Redirigir usando un formulario auto-enviable para pasar datos por POST
        echo "<body onload=\"document.getElementById('redirectForm').submit()\">
                <form id='redirectForm' action='generar_certificado.php' method='post' style='display:none;'>";
        foreach ($_SESSION['cert_data_alumnos'] as $key => $value) {
            if (is_array($value)) {
                foreach ($value as $cuil => $data) {
                    if (isset($data['cuil'])) {
                        echo "<input type='hidden' name='alumnos[{$cuil}][cuil]' value='" . htmlspecialchars($data['cuil']) . "'>";
                        echo "<input type='hidden' name='alumnos[{$cuil}][estado]' value='" . htmlspecialchars($data['estado']) . "'>";
                    }
                }
            } else {
                echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
            }
        }
        foreach ($cert_data_for_pdf as $key => $value) {
            if ($key === 'files') continue;
            echo "<input type='hidden' name='" . htmlspecialchars($key) . "' value='" . htmlspecialchars($value) . "'>";
        }
        foreach ($uploaded_files as $file_key => $file_path) {
            echo "<input type='hidden' name='files[{$file_key}]' value='" . htmlspecialchars($file_path) . "'>";
        }
        echo "</form><p>Redirigiendo para procesar los certificados...</p></body>";
        exit();
    }
}

$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subir Archivos para Certificado</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>subir_archivos.css">
    <style>
        .dropzone.dragover {
            border-color: #3498db;
            background-color: #f0f8ff;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="header-container">
        <div class="logo"><a href="<?php echo $base_path; ?>index.html"><img src="<?php echo $img_path; ?>UTNLogo.png" alt="Logo UTN FRH"></a></div>
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
        <button class="hamburger-menu" aria-label="Abrir menú"><span class="hamburger-line"></span><span class="hamburger-line"></span><span class="hamburger-line"></span></button>
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

<main class="admin-section">
    <div class="admin-container">
        <h1 class="main-title">Archivos para el Certificado</h1>
        <p class="sub-title">Sube las firmas y logos necesarios para generar los certificados.</p>

        <?php if (!empty($error_messages)): ?>
            <div class="error-container institutional-error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <p><strong>Por favor, corrija los siguientes errores:</strong></p>
                    <ul>
                        <?php foreach ($error_messages as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form action="subir_archivos_certificado.php" method="post" enctype="multipart/form-data" id="upload-form">
            <input type="hidden" name="submit_files" value="1">

            <div class="form-group">
                <label for="tipo_certificado">Tipo de Certificado <span class="required">*</span></label>
                <select name="tipo_certificado" id="tipo_certificado" required class="form-control">
                    <option value="">Seleccione una opción</option>
                    <option value="genuino" <?php echo (isset($_POST['tipo_certificado']) && $_POST['tipo_certificado'] == 'genuino') ? 'selected' : ''; ?>>Genuino</option>
                    <option value="externo" <?php echo (isset($_POST['tipo_certificado']) && $_POST['tipo_certificado'] == 'externo') ? 'selected' : ''; ?>>Externo</option>
                </select>
            </div>

            <!-- Campos para Genuino -->
            <div id="genuino-fields" style="display: none; width: 100%; max-width: 600px;">
                <div class="form-group">
                    <label for="tipo_actividad_genuino">Tipo de actividad <span class="required">*</span></label>
                    <input type="text" name="tipo_actividad" id="tipo_actividad_genuino" class="form-control">
                </div>
                <div class="form-group">
                    <label for="firma_secretario_genuino">Firma del Secretario (PNG) <span class="required">*</span></label>
                    <div class="dropzone" id="dz-firma-secretario-genuino"><div class="dz-message">Arrastra o haz clic para subir</div></div>
                    <input type="file" name="firma_secretario" id="firma_secretario_genuino" accept="image/png" style="display:none;">
                </div>
                <div class="form-group">
                    <label for="logo_institucional_genuino">Logo Institucional (PNG) <span class="required">*</span></label>
                    <div class="dropzone" id="dz-logo-institucional-genuino"><div class="dz-message">Arrastra o haz clic para subir</div></div>
                    <input type="file" name="logo_institucional" id="logo_institucional_genuino" accept="image/png" style="display:none;">
                </div>
                <div class="form-group">
                    <label for="firma_docente">Firma del Docente (PNG) <span class="required">*</span></label>
                    <div class="dropzone" id="dz-firma-docente"><div class="dz-message">Arrastra o haz clic para subir</div></div>
                    <input type="file" name="firma_docente" id="firma_docente" accept="image/png" style="display:none;">
                </div>
            </div>

            <!-- Campos para Externo -->
            <div id="externo-fields" style="display: none; width: 100%; max-width: 600px;">
                <div class="form-group">
                    <label for="tipo_actividad_externo">Tipo de actividad <span class="required">*</span></label>
                    <input type="text" name="tipo_actividad" id="tipo_actividad_externo" class="form-control">
                </div>
                <div class="form-group">
                    <label for="camara_organizadora">Nombre de la cámara organizadora <span class="required">*</span></label>
                    <input type="text" name="camara_organizadora" id="camara_organizadora" class="form-control">
                </div>
                <div class="form-group">
                    <label for="institutos_codictantes">Nombre instituto/s codictante/s <span class="required">*</span></label>
                    <input type="text" name="institutos_codictantes" id="institutos_codictantes" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="firma_secretario_externo">Firma del Secretario (PNG) <span class="required">*</span></label>
                    <div class="dropzone" id="dz-firma-secretario-externo"><div class="dz-message">Arrastra o haz clic para subir</div></div>
                    <input type="file" name="firma_secretario" id="firma_secretario_externo" accept="image/png" style="display:none;">
                </div>
                <div class="form-group">
                    <label for="logo_institucional_externo">Logo Institucional (PNG) <span class="required">*</span></label>
                    <div class="dropzone" id="dz-logo-institucional-externo"><div class="dz-message">Arrastra o haz clic para subir</div></div>
                    <input type="file" name="logo_institucional" id="logo_institucional_externo" accept="image/png" style="display:none;">
                </div>
                <div class="form-group">
                    <label for="firma_autoridad">Firma del Docente/Director (PNG) <span class="required">*</span></label>
                    <div class="dropzone" id="dz-firma-autoridad"><div class="dz-message">Arrastra o haz clic para subir</div></div>
                    <input type="file" name="firma_autoridad" id="firma_autoridad" accept="image/png" style="display:none;">
                </div>
                <div class="form-group checkbox-container">
                    <input type="checkbox" name="es_director" id="es_director" value="1">
                    <label for="es_director" style="margin-bottom: 0;">La firma corresponde a un director</label>
                </div>
            </div>

            <div class="form-buttons">
                <button type="button" class="btn-cancelar-cert" onclick="window.location.href='seleccionar_alum_certif.php'">Volver</button>
                <button type="submit" class="submit-btn">Continuar</button>
            </div>
        </form>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Prevent browser from opening dropped files globally
    document.body.addEventListener('dragover', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });
    document.body.addEventListener('drop', function(e) {
        e.preventDefault();
        e.stopPropagation();
    });

    const uploadForm = document.getElementById('upload-form');
    const tipoCertificado = document.getElementById('tipo_certificado');
    const genuinoFields = document.getElementById('genuino-fields');
    const externoFields = document.getElementById('externo-fields');

    // Inputs de texto
    const tipoActividadGenuino = document.getElementById('tipo_actividad_genuino');
    const tipoActividadExterno = document.getElementById('tipo_actividad_externo');

    function toggleFields() {
        const selectedType = tipoCertificado.value;
        genuinoFields.style.display = 'none';
        externoFields.style.display = 'none';
        disableAllInputs(genuinoFields);
        disableAllInputs(externoFields);

        if (selectedType === 'genuino') {
            genuinoFields.style.display = 'block';
            enableAllInputs(genuinoFields);
        } else if (selectedType === 'externo') {
            externoFields.style.display = 'block';
            enableAllInputs(externoFields);
        }
    }

    function disableAllInputs(container) {
        container.querySelectorAll('input, select').forEach(input => input.disabled = true);
    }

    function enableAllInputs(container) {
        container.querySelectorAll('input, select').forEach(input => input.disabled = false);
    }

    tipoCertificado.addEventListener('change', toggleFields);

    // Sincronizar campos de texto comunes en tiempo real
    tipoActividadGenuino.addEventListener('input', () => tipoActividadExterno.value = tipoActividadGenuino.value);
    tipoActividadExterno.addEventListener('input', () => tipoActividadGenuino.value = tipoActividadExterno.value);

    // Lógica para los dropzones
    function setupDropzone(dropzoneId, inputId) {
        const dropzone = document.getElementById(dropzoneId);
        const fileInput = document.getElementById(inputId);
        if (!dropzone || !fileInput) return; // Defensive check

        const messageElement = dropzone.querySelector('.dz-message');
        const originalMessage = messageElement.innerHTML;

        dropzone.addEventListener('click', (e) => {
            if (e.target.classList.contains('cancel-upload')) {
                e.stopPropagation();
                fileInput.value = ''; // Limpiar el input de archivo
                fileInput.dispatchEvent(new Event('change')); // Disparar evento para sincronizar
            } else {
                fileInput.click();
            }
        });

        // Eventos de arrastrar y soltar
        dropzone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        });

        dropzone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        });

        dropzone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                fileInput.dispatchEvent(new Event('change')); // Disparar evento para actualizar UI y sincronizar
            }
        });

        fileInput.addEventListener('change', () => {
            updateDropzoneMessage(fileInput, messageElement, originalMessage);
            syncFileInputs(fileInput);
        });
    }
    
    function updateDropzoneMessage(fileInput, messageElement, originalMessage) {
        if (fileInput.files.length > 0) {
            messageElement.innerHTML = `
                <span style="display: flex; align-items: center; justify-content: center; flex-wrap: wrap;">
                    <i class="fas fa-check-circle" style="color: green; margin-right: 8px;"></i>
                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">${fileInput.files[0].name}</span>
                    <i class="fas fa-times-circle cancel-upload" style="color: red; cursor: pointer; margin-left: 8px;" title="Quitar archivo"></i>
                </span>`;
        } else {
            messageElement.innerHTML = originalMessage;
        }
    }

    function syncFileInputs(changedInput) {
        const isGenuino = changedInput.id.includes('genuino');
        const isExterno = changedInput.id.includes('externo');
        let correspondingInput;

        if (isGenuino) {
            const baseId = changedInput.id.replace('_genuino', '');
            correspondingInput = document.getElementById(baseId + '_externo');
        } else if (isExterno) {
            const baseId = changedInput.id.replace('_externo', '');
            correspondingInput = document.getElementById(baseId + '_genuino');
        }

        if (correspondingInput && correspondingInput.files !== changedInput.files) {
            correspondingInput.files = changedInput.files;
            const correspondingDropzone = correspondingInput.previousElementSibling;
            const correspondingMessage = correspondingDropzone.querySelector('.dz-message');
            const originalMessage = 'Arrastra o haz clic para subir';
            updateDropzoneMessage(correspondingInput, correspondingMessage, originalMessage);
        }
    }

    // Configurar todos los dropzones
    ['firma_secretario', 'logo_institucional'].forEach(name => {
        setupDropzone(`dz-${name.replace('_', '-')}-genuino`, `${name}_genuino`);
        setupDropzone(`dz-${name.replace('_', '-')}-externo`, `${name}_externo`);
    });
    setupDropzone('dz-firma-docente', 'firma_docente');
    setupDropzone('dz-firma-autoridad', 'firma_autoridad');

    // Validación de formulario en el envío
    uploadForm.addEventListener('submit', function(e) {
        const selectedType = tipoCertificado.value;
        let errors = [];

        if (selectedType === 'externo') {
            const institutos = document.getElementById('institutos_codictantes');
            if (!institutos.value.trim()) {
                errors.push("El campo 'Nombre instituto/s codictante/s' es obligatorio.");
            }
        }
        
        // Se puede agregar más validación de JS aquí si es necesario

        if (errors.length > 0) {
            e.preventDefault();
            alert("Por favor, corrija los siguientes errores:\n\n" + errors.join("\n"));
        }
    });

    // Ejecutar al cargar la página por si hay un valor preseleccionado
    toggleFields();
});
</script>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-logo-info">
            <img src="<?php echo $img_path; ?>UTNLogo_footer.webp" alt="Logo UTN" class="footer-logo">
            <div class="footer-info"><p>París 532, Haedo (1706)</p><p>Buenos Aires, Argentina</p><br><p>Número de teléfono del depto.</p><br><p>extension@frh.utn.edu.ar</p></div>
        </div>
        <div class="footer-social-legal">
            <div class="footer-social"><a href="#"><i class="fab fa-youtube"></i></a><a href="#"><i class="fab fa-linkedin"></i></a></div>
            <div class="footer-legal"><a href="#">Contacto</a><br><a href="#">Políticas de Privacidad</a></div>
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
    </div>
</footer>
<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>
<script src="<?php echo $js_path; ?>general.js"></script>
</body>
</html>