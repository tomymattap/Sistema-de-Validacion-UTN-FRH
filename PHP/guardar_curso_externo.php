<?php
session_start();
include 'conexion.php';

// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $modalidad = $_POST['modalidad'] ?? '';
    $docente = $_POST['docente'] ?? null;
    $carga = $_POST['carga'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $requisitos = $_POST['requisitos'] ?? null;
    $categoria = $_POST['categoria'] ?? '';
    $instituciones_externas = $_POST['instituciones_externas'] ?? '';
    $estado_evaluacion = 'PENDIENTE';
    $tipo = "CERTIFICACIÓN";

    mysqli_begin_transaction($conexion);

    /*
    echo '<pre>';
    print_r($_FILES);
    echo '</pre>';
    exit;*/
    
    try {
    // 1️⃣ Insertar el curso
    $sql_insert = "INSERT INTO curso 
        (Nombre_Curso, Modalidad, Docente, Carga_Horaria, Descripcion, Requisitos, Categoria, Tipo)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = mysqli_prepare($conexion, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "sssissss", 
        $nombre, $modalidad, $docente, $carga, $descripcion, $requisitos, $categoria, $tipo
    );
    mysqli_stmt_execute($stmt_insert);

    $id_curso = mysqli_insert_id($conexion);

    // 2️⃣ Procesar el archivo PDF (guardar como LONGBLOB)
    $archivo_evaluacion = null;

    if (
        isset($_FILES['archivo_evaluacion']) &&
        $_FILES['archivo_evaluacion']['error'] === UPLOAD_ERR_OK
    ) {
        // Validar tipo MIME
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($_FILES['archivo_evaluacion']['tmp_name']);

        if ($mime_type !== 'application/pdf') {
            throw new Exception("El archivo debe ser un PDF válido.");
        }

        // Leer el contenido binario directamente
        $archivo_evaluacion = file_get_contents($_FILES['archivo_evaluacion']['tmp_name']);
    }

    // 3️⃣ Invalidar token de acceso
    $sql_update = "UPDATE acceso_externo SET usado = 1 WHERE token = ?";
    $stmt_update = mysqli_prepare($conexion, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "s", $token);
    mysqli_stmt_execute($stmt_update);

    // 4️⃣ Insertar en evaluacion_curso_externo
    $sql_evaluacion = "INSERT INTO evaluacion_curso_externo 
        (ID_Curso, Estado_Evaluacion, Archivo_Evaluacion, instituciones_externas)
        VALUES (?, ?, ?, ?)";
    $stmt_evaluacion = mysqli_prepare($conexion, $sql_evaluacion);

    $null = NULL;
    mysqli_stmt_bind_param(
        $stmt_evaluacion,
        "isbs",
        $id_curso,
        $estado_evaluacion,
        $null,
        $instituciones_externas
    );

    // Si hay un archivo, se envía como dato largo para el segundo marcador de posición '?' (índice 2)
    if ($archivo_evaluacion !== null) {
        mysqli_stmt_send_long_data($stmt_evaluacion, 2, $archivo_evaluacion);
    }

    mysqli_stmt_execute($stmt_evaluacion);

    // 5️⃣ Confirmar transacción
    mysqli_commit($conexion);

    $_SESSION['status_message'] = "El curso '" . htmlspecialchars($nombre) . "' fue registrado correctamente.";
    $_SESSION['status_type'] = 'success';

} catch (Exception $e) {
    mysqli_rollback($conexion);
    $_SESSION['status_message'] = "Error: " . $e->getMessage();
    $_SESSION['status_type'] = 'error';
}
    header('Location: guardar_curso_externo.php');
    exit;
}



// --- VISUALIZACIÓN DEL RESULTADO (MÉTODO GET) ---
$status_message = $_SESSION['status_message'] ?? null;
$status_type = $_SESSION['status_type'] ?? null;

unset($_SESSION['status_message']);
unset($_SESSION['status_type']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Registro de Curso</title>
    <link rel="icon" href="../Imagenes/icon.png" type="image/png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/ADMIN/gestionar_cursos.css">
</head>

<body class="fade-in">
    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../index.html"><img src="../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
        </div>
    </header>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <div class="contenido-principal">

                <?php if ($status_message && $status_type === 'success'): ?>
                    <div class="confirmacion-container" style="max-width: 700px; margin: 2rem auto;">
                        <div class="confirmacion-header" style="justify-content: center;">
                            <i class="fas fa-check-circle"></i>
                            <h3>Registro Exitoso</h3>
                        </div>
                        <p style="text-align: center; font-size: 1.1rem;"><?= htmlspecialchars($status_message) ?></p>
                        <p style="text-align: center;">Gracias por su colaboración. El token de acceso ha sido utilizado y ya no es válido.</p>
                        <p style="text-align: center; font-size: 0.95rem; color: #666;">Será redirigido a la página principal en <span id="countdown">5</span> segundos...</p>
                    </div>

                <?php elseif ($status_message && $status_type === 'error'): ?>
                    <div class="confirmacion-container" style="border-left-color: var(--color-secundario-4); max-width: 700px; margin: 2rem auto;">
                        <div class="confirmacion-header" style="justify-content: center;">
                            <i class="fas fa-times-circle" style="color: var(--color-secundario-4);"></i>
                            <h3>Error en el Registro</h3>
                        </div>
                        <p style="text-align: center; font-size: 1.1rem;"><?= htmlspecialchars($status_message) ?></p>
                        <div class="form-actions centered-actions" style="margin-top: 2rem;">
                            <a href="../index.html" class="menu-btn volver-btn">Volver a la Página Principal</a>
                        </div>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </main>

    <footer class="site-footer"></footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../JavaScript/general.js"></script>

    <?php if ($status_message && $status_type === 'success'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let countdownElement = document.getElementById('countdown');
            let timeLeft = 5;
            let countdownInterval = setInterval(function() {
                timeLeft--;
                countdownElement.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(countdownInterval);
                    window.location.href = '../index.html';
                }
            }, 1000);
        });
    </script>
    <?php endif; ?>
</body>
</html>
