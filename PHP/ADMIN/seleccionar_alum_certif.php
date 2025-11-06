<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Validar que solo los administradores puedan acceder
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    header('Location: ../iniciosesion.php?error=acceso_denegado');
    exit;
}

include("../conexion.php");

// Consultamos todos los cursos
$consulta = "SELECT ID_Curso, Nombre_Curso FROM CURSO";
$resultado = mysqli_query($conexion, $consulta);

// --- Definición de rutas ---
$base_path = '../../';
$css_path = $base_path . 'CSS/';
$img_path = $base_path . 'Imagenes/';
$js_path = $base_path . 'JavaScript/';
$html_path = $base_path . 'HTML/';
$php_path = $base_path . 'PHP/';
$current_page = 'seleccionar_alum_certif.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Emitir Certificados - Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>general.css">
    <link rel="stylesheet" href="<?php echo $css_path; ?>emitircertificados.css">
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
                <li><a href="<?php echo $html_path; ?>sobrenosotros.php">SOBRE NOSOTROS</a></li>
                <li><a href="<?php echo $html_path; ?>contacto.php">CONTACTO</a></li>
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
            <li><a href="<?php echo $html_path; ?>sobrenosotros.php">SOBRE NOSOTROS</a></li>
            <li><a href="<?php echo $html_path; ?>contacto.php">CONTACTO</a></li>
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
        <h1 class="main-title" style="text-align: center;">Emitir Certificados</h1>
        <div class="certificate-form-container" style="margin: 0 auto; width: 40%;">
            <h2>Seleccione curso, año y cuatrimestre</h2>

            <form action="tabla_alumnos_certif.php" method="POST">
                <!-- Curso -->
                <div class="form-group">
                    <label for="curso">Curso:</label>
                    <select name="curso" id="curso" required> 
                        <option value="" disabled selected>Seleccione un curso</option>
                        <?php
                        while ($fila = mysqli_fetch_assoc($resultado)) {
                            echo "<option value='" . htmlspecialchars($fila['ID_Curso']) . "'>" . htmlspecialchars($fila['Nombre_Curso']) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Año -->
                <div class="form-group">
                    <label for="anio">Año:</label>
                    <input type="number" id="anio" name="anio" min="2020" max="2099" required>
                </div>

                <!-- Cuatrimestre -->
                <div class="form-group">
                    <label for="cuatrimestre">Cuatrimestre:</label>
                    <select id="cuatrimestre" name="cuatrimestre" required>
                        <option value="" disabled selected>Seleccione un cuatrimestre</option>
                        <option value="Primer Cuatrimestre">Primer Cuatrimestre</option>
                        <option value="Segundo Cuatrimestre">Segundo Cuatrimestre</option>
                    </select>
                </div>

                <div class="form-buttons">
                    <button type="submit">Continuar</button>
                    <button type="reset" class="reset-btn">Limpiar</button>
                </div>
            </form>
        </div>
    </div>
</main>

<footer class="site-footer">
    <!-- Contenido del pie de página -->
</footer>

<a href="#" class="scroll-to-top-btn" id="scroll-to-top-btn" aria-label="Volver arriba">
    <i class="fas fa-arrow-up"></i>
</a>

<script src="<?php echo $js_path; ?>general.js"></script>
<script src="<?php echo $js_path; ?>emitircertificados.js"></script>

</body>
</html>
