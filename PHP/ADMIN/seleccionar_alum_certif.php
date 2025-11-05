<?php
$page_title = 'Emitir Certificados - Admin';
$extra_styles = ['emitircertificados.css', 'validacion.css'];
include('../header.php');

// La validación de sesión ya está en el header
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 1) {
    echo '<script>window.location.href = "' . htmlspecialchars($base_path) . 'PHP/iniciosesion.php?error=acceso_denegado";</script>';
    exit;
}

include("../conexion.php");

// Consultamos todos los cursos
$consulta = "SELECT ID_Curso, Nombre_Curso FROM CURSO";
$resultado = mysqli_query($conexion, $consulta);
?>
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
<?php
$extra_scripts = ['emitircertificados.js'];
include('../footer.php');
?>
