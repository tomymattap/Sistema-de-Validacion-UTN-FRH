<?php
$page_title = 'Perfil de Alumno - UTN FRH';
$extra_styles = ['perfil.css'];
include('../header.php');

// Verificar si el usuario está logueado y es un alumno
if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 2) {
    echo '<script>window.location.href = "' . htmlspecialchars($base_path) . 'PHP/iniciosesion.php?error=acceso_denegado";</script>';
    exit();
}

require '../conexion.php';
$user_id = $_SESSION['user_id'];

// Obtener datos del alumno
$sql = "SELECT Nombre_Alumno, Apellido_Alumno, DNI_Alumno, Email_Alumno, Telefono FROM alumno WHERE ID_Cuil_Alumno = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$alumno = $result->fetch_assoc();

$stmt->close();
$conexion->close();
?>
    <main>
        <div class="profile-container">
            <h1>Perfil de Alumno</h1>
            <div class="profile-info">
                <div class="profile-field">
                    <label for="nombre">Nombre</label>
                    <div class="value non-editable">
                        <span id="nombre"><?php echo htmlspecialchars($alumno['Nombre_Alumno']); ?></span>
                    </div>
                </div>
                <div class="profile-field">
                    <label for="apellido">Apellido</label>
                    <div class="value non-editable">
                        <span id="apellido"><?php echo htmlspecialchars($alumno['Apellido_Alumno']); ?></span>
                    </div>
                </div>
                <div class="profile-field">
                    <label for="dni">DNI</label>
                    <div class="value non-editable">
                        <span id="dni"><?php echo htmlspecialchars($alumno['DNI_Alumno']); ?></span>
                    </div>
                </div>
                <div class="profile-field">
                    <label for="email">Correo electrónico</label>
                    <div class="value">
                        <span id="email"><?php echo htmlspecialchars($alumno['Email_Alumno']); ?></span>
                        <button class="edit-btn"><i class="fas fa-pencil-alt"></i></button>
                    </div>
                </div>
                <div class="profile-field">
                    <label for="telefono">Número de contacto</label>
                    <div class="value">
                        <span id="telefono"><?php echo htmlspecialchars($alumno['Telefono']); ?></span>
                        <button class="edit-btn"><i class="fas fa-pencil-alt"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php
$extra_scripts = ['perfil.js'];
include('../footer.php');
?>