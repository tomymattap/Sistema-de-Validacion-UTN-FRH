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
                        <form action="guardar_curso_externo.php" method="POST" class="form-grid">
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
                                <label for="archivo_evaluacion">Archivo de Evaluación (Opcional)</label>
                                <input type="file" id="archivo_evaluacion" name="archivo_evaluacion">
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
</body>
</html>
