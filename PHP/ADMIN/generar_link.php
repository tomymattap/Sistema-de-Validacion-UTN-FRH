<?php
session_start();
include '../conexion.php';

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../inicio_sesion.php?error=acceso_denegado');
    exit;
}

$link = null;
$error_message = '';

try {
    // Generar token único
    $token = bin2hex(random_bytes(16));

    // Guardarlo en la base con expiración (por ejemplo, 3 días)
    $sql = "INSERT INTO acceso_externo (token, valido_hasta)
            VALUES (?, DATE_ADD(NOW(), INTERVAL 3 DAY))";
    
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "s", $token);

    if (mysqli_stmt_execute($stmt)) {
        $link = "http://localhost/Sistema-de-Validacion-UTN-FRH/PHP/externo_form.php?token=$token";
    } else {
        throw new Exception("Error al generar el enlace en la base de datos.");
    }
} catch (Exception $e) {
    $error_message = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generar Enlace Externo - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/general.css">
    <link rel="stylesheet" href="../../CSS/ADMIN/gestionar_cursos.css">
</head>
<body class="fade-in">
    <header class="site-header">
        <div class="header-container">
            <div class="logo"><a href="../../index.html"><img src="../../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a></div>
        </div>
    </header>

    <main class="admin-section" style="padding-top: 2rem; padding-bottom: 2rem;">
        <div class="gestion-cursos-container">
            <div class="contenido-principal">

                <div id="header-container">
                    <h1 class="main-title" style="color: transparent; user-select: none;">.</h1>
                    <a href="gestion_externos.php" class="menu-btn volver-btn"><i class="fas fa-arrow-left"></i> VOLVER</a>
                </div>

                <div class="link-generation-container">
                    <?php if ($error_message): ?>
                        <div class="confirmacion-header" style="justify-content: center;">
                            <i class="fas fa-times-circle" style="color: var(--color-secundario-4);"></i>
                            <h3>Error al generar el enlace</h3>
                        </div>
                        <p style="text-align: center;"><?= htmlspecialchars($error_message) ?></p>
                    <?php elseif ($link): ?>
                        <div class="confirmacion-header" style="justify-content: center;">
                            <i class="fas fa-check-circle"></i>
                            <h3>Enlace generado correctamente</h3>
                        </div>
                        <p style="text-align: center;">Copia y comparte este enlace para permitir el registro de un nuevo curso de tipo "Certificación". El enlace será válido por 3 días.</p>
                        
                        <div class="link-input-group">
                            <input type="text" id="generated-link" value="<?= htmlspecialchars($link) ?>" readonly>
                            <button id="copy-link-btn" title="Copiar al portapapeles"><i class="fas fa-copy"></i> Copiar</button>
                        </div>
                        <span id="copy-feedback" class="copy-feedback"></span>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="footer-container">
            <div class="footer-info" style="text-align: center; width: 100%;">
                <p>París 532, Haedo (1706) | Buenos Aires, Argentina</p>
                <p>extension@frh.utn.edu.ar</p>
            </div>
        </div>
    </footer>
    <a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba"><i class="fas fa-arrow-up"></i></a>

    <script src="../../JavaScript/general.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const copyBtn = document.getElementById('copy-link-btn');
            const linkInput = document.getElementById('generated-link');
            const feedback = document.getElementById('copy-feedback');

            if (copyBtn && linkInput) {
                copyBtn.addEventListener('click', function() {
                    linkInput.select();
                    document.execCommand('copy');
                    
                    feedback.textContent = '¡Copiado!';
                    feedback.classList.add('visible');

                    setTimeout(() => {
                        feedback.classList.remove('visible');
                    }, 2000);
                });
            }
        });
    </script>
</body>
</html>
