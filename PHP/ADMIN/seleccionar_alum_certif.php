<?php
include("conexion.php");

// Consultamos todos los cursos
$consulta = "SELECT ID_Curso, Nombre_Curso FROM CURSO";
$resultado = mysqli_query($conexion, $consulta);
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
    <link rel="stylesheet" href="../CSS/general.css">
    <link rel="stylesheet" href="../CSS/emitircertificados.css">
    <link rel="stylesheet" href="../CSS/validacion.css">
</head>
<body class="fade-in">
    <div class="preloader">
        <div class="spinner"></div>
    </div>

    <header class="site-header">
        <div class="header-container">
            <div class="logo">
                <a href="../index.html"><img src="../Imagenes/UTNLogo.png" alt="Logo UTN FRH"></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="../index.html">VALIDAR</a></li>
                    <!--<li> <a href="HTML/cursos.html">CURSOS</a> </li>-->
                    <li><a href="sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="contacto.html">CONTACTO</a></li>
                </ul>
            </nav>
            <div class="session-controls" id="session-controls">
                <button class="user-menu-toggle">Hola, Admin. <i class="fas fa-chevron-down"></i></button>
                <div class="dropdown-menu">
                    <ul>
                        <li><a href="../HTML/verinscriptos.html">Ver Inscriptos</a></li>
                        <li><a href="../HTML/gestionarcursos.html">Gestionar Cursos</a></li>
                        <li><a href="seleccionar_alum_certif.php">Emitir Certificados</a></li>
                        <li><a href="#">Cerrar Sesión</a></li>
                    </ul>
                </div>
            </div>
            <button class="hamburger-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
        <div class="mobile-nav">
            <button class="close-menu" aria-label="Cerrar menú">&times;</button>
            <nav>
                <ul>
                    <li><a href="../index.html">INICIO</a></li>
                    <!--<li> <a href="HTML/cursos.html">CURSOS</a> </li>-->
                    <li><a href="sobrenosotros.html">SOBRE NOSOTROS</a></li>
                    <li><a href="contacto.html">CONTACTO</a></li>
                </ul>
                <div class="mobile-session-controls" id="mobile-session-controls">
                    <!-- Contenido dinámico por JS -->
                </div>
            </nav>
        </div>
    </header>

    <main class="admin-section" style="padding-top: 4rem; padding-bottom: 4rem;">
    <div class="admin-container">
        <h1 class="main-title" style="text-align: center;">Emitir Certificados</h1>
        <div class="certificate-form-container" style="margin: 0 auto; width: 40%;">
            <h2>Seleccione curso, año y cuatrimestre</h2>

            <form action="PHP\ADMIN\tabla_alumnos_certif.php" method="POST">
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
        <!-- Footer content -->
    </footer>

    <script src="https://cdn.rawgit.com/davidshimjs/qrcodejs/gh-pages/qrcode.min.js"></script>
    <script src="../JavaScript/general.js"></script>
    <script src="../JavaScript/emitircertificados.js"></script>
    <a href="#" class="scroll-to-top-btn" title="Volver arriba"><i class="fas fa-arrow-up"></i></a>
</body>
</html>
