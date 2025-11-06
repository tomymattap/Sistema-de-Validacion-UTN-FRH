<?php
$page_title = 'Inscripciones - UTN FRH';
$extra_styles = ['inscripciones.css'];
include('../header.php');

// Verificar si el usuario está logueado y es un alumno
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    echo '<script>window.location.href = "' . htmlspecialchars($base_path) . 'PHP/iniciosesion.php?error=acceso_denegado";</script>';
    exit();
}
?>
    <main class="inscripciones-container">
        <div class="cursos-list">
            <h1>INSCRIPCIONES</h1>
            <h2>Mis cursos</h2>
            <div class="cursos-accordion">
                <div class="curso-item" data-course-id="curso1">
                    <div class="curso-header">
                        <h3>Instalación de Paneles Solares</h3>
                    </div>
                    <div class="curso-details">
                        <p><strong>Fecha de inicio:</strong> 01/08/2024</p>
                        <p><strong>Fecha de finalización:</strong> 30/11/2024</p>
                        <p><strong>Modalidad:</strong> Online</p>
                        <p><strong>Docente a Cargo:</strong> Juan Pérez</p>
                    </div>
                </div>
                <div class="curso-item" data-course-id="curso2">
                    <div class="curso-header">
                        <h3>Programming Essentials en Python</h3>
                    </div>
                    <div class="curso-details">
                        <p><strong>Fecha de inicio:</strong> 15/08/2024</p>
                        <p><strong>Fecha de finalización:</strong> 15/12/2024</p>
                        <p><strong>Modalidad:</strong> Presencial</p>
                        <p><strong>Docente a Cargo:</strong> María García</p>
                    </div>
                </div>
            </div>
        </div>
        <aside class="estado-aside">
            <h2>Estado</h2>
            <ul>
                <li id="estado-pendiente">Pendiente</li>
                <li id="estado-aceptado">Aceptado</li>
                <li id="estado-finalizado">Finalizado</li>
            </ul>
        </aside>
    </main>
<?php
$extra_scripts = ['inscripciones.js'];
include('../footer.php');
?>