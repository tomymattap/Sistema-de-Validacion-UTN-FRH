<?php
$page_title = 'Certificaciones - UTN FRH';
$extra_styles = ['certificaciones.css'];
include('../header.php');

// Verificar si el usuario está logueado y es un alumno
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    echo '<script>window.location.href = "' . htmlspecialchars($base_path) . 'PHP/iniciosesion.php?error=acceso_denegado";</script>';
    exit();
}
?>
    <main>
        <div class="certificaciones-table-container">
            <h1>CERTIFICADOS</h1>
            <table class="certificaciones">
                <thead>
                    <tr>
                        <th>CURSO</th>
                        <th>ESTADO</th>
                        <th>FECHA DE EMISIÓN</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Instalación de Paneles Solares</td>
                        <td>Aprobado</td>
                        <td>00/00/0000</td>
                        <td><a href="#" class="action-btn">Ver Certificado</a></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </main>
<?php
include('../footer.php');
?>