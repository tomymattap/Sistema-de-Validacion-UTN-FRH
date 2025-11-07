<?php
session_start();
include 'conexion.php';

// --- PROCESAMIENTO DEL FORMULARIO (MÉTODO POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'] ?? '';
    // ... (resto de la recolección de datos)
    $nombre = $_POST['nombre'] ?? '';
    $modalidad = $_POST['modalidad'] ?? '';
    $docente = $_POST['docente'] ?? null;
    $carga = $_POST['carga'] ?? 0;
    $descripcion = $_POST['descripcion'] ?? '';
    $requisitos = $_POST['requisitos'] ?? null;
    $categoria = $_POST['categoria'] ?? '';
    $tipo = "CERTIFICACIÓN";
    
    mysqli_begin_transaction($conexion);
    try {
        $sql_insert = "INSERT INTO curso 
            (Nombre_Curso, Modalidad, Docente, Carga_Horaria, Descripcion, Requisitos, Categoria, Tipo)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conexion, $sql_insert);
        mysqli_stmt_bind_param($stmt_insert, "sssissss", $nombre, $modalidad, $docente, $carga, $descripcion, $requisitos, $categoria, $tipo);
        if (!mysqli_stmt_execute($stmt_insert)) {
            throw new Exception("Error al registrar el curso: " . mysqli_stmt_error($stmt_insert));
        }

        $sql_update = "UPDATE acceso_externo SET usado = 1 WHERE token = ?";
        $stmt_update = mysqli_prepare($conexion, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "s", $token);
        if (!mysqli_stmt_execute($stmt_update)) {
            throw new Exception("Error al invalidar el token de acceso.");
        }

        mysqli_commit($conexion);
        
        // Guardar mensaje de éxito en la sesión
        $_SESSION['status_message'] = "El curso '" . htmlspecialchars($nombre) . "' ha sido registrado correctamente.";
        $_SESSION['status_type'] = 'success';

    } catch (Exception $e) {
        mysqli_rollback($conexion);
        // Guardar mensaje de error en la sesión
        $_SESSION['status_message'] = $e->getMessage();
        $_SESSION['status_type'] = 'error';
    }

    // Redirigir a la misma página para mostrar el resultado (Patrón PRG)
    header('Location: guardar_curso_externo.php');
    exit;
}

// --- VISUALIZACIÓN DEL RESULTADO (MÉTODO GET) ---
$status_message = $_SESSION['status_message'] ?? null;
$status_type = $_SESSION['status_type'] ?? null;

// Limpiar los mensajes de la sesión para que no se muestren de nuevo al recargar
unset($_SESSION['status_message']);
unset($_SESSION['status_type']);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Registro de Curso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/gestionar_cursos.css">
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
