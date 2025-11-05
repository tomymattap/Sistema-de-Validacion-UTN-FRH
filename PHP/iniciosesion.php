<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $login_input = $_POST['login-input'];
    $password = $_POST['password'];

    if (empty($login_input) || empty($password)) {
        header("Location: ../HTML/iniciosesion.html?error=camposvacios");
        exit();
    }

    // 1. Buscar en la tabla de administradores por legajo
    $sql_admin = "SELECT * FROM admin WHERE Legajo = ?";
    $stmt_admin = $conexion->prepare($sql_admin);
    $stmt_admin->bind_param("s", $login_input);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin->num_rows == 1) {
        $admin = $result_admin->fetch_assoc();
        if ($password == $admin['Password']) {
            $_SESSION['user_id'] = $admin['ID_Admin'];
            $_SESSION['user_legajo'] = $admin['Legajo'];
            $_SESSION['user_rol'] = 1; // Rol de Administrador
            $_SESSION['user_name'] = $admin['Nombre'];

            header("Location: ADMIN/verinscriptos.php");
            exit();
        }
    }
    $stmt_admin->close();

    // 2. Si no es admin, buscar en la tabla de alumnos por cuil
    $sql_alumno = "SELECT * FROM alumno WHERE ID_Cuil_Alumno = ?";
    $stmt_alumno = $conexion->prepare($sql_alumno);
    $stmt_alumno->bind_param("s", $login_input);
    $stmt_alumno->execute();
    $result_alumno = $stmt_alumno->get_result();

    if ($result_alumno->num_rows == 1) {
        $alumno = $result_alumno->fetch_assoc();
        if ($password == $alumno['Password']) {
            $_SESSION['user_id'] = $alumno['ID_Cuil_Alumno'];
            $_SESSION['user_rol'] = 2; // Rol de Alumno
            $_SESSION['user_name'] = $alumno['Nombre_Alumno'];

            header("Location: ALUMNO/perfil.php");
            exit();
        }
    }
    $stmt_alumno->close();

    // 3. Si no se encontró en ninguna tabla o la contraseña fue incorrecta
    header("Location: ../HTML/iniciosesion.html?error=credencialesinvalidas");
    exit();

    $conexion->close();
} else {
    header("Location: ../HTML/iniciosesion.html");
    exit();
}
?>