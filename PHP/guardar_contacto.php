<?php
include("conexion.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rol = mysqli_real_escape_string($conexion, $_POST['rol']);
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellido = mysqli_real_escape_string($conexion, $_POST['apellido']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $mensaje = mysqli_real_escape_string($conexion, $_POST['mensaje']);

    $query = "INSERT INTO contacto (Rol, Nombre, Apellido, Email, Mensaje)
              VALUES ('$rol', '$nombre', '$apellido', '$email', '$mensaje')";

    if (mysqli_query($conexion, $query)) {
        echo "✅ Datos guardados correctamente en la base de datos.";
    } else {
        echo "❌ Error al guardar los datos: " . mysqli_error($conexion);
    }

    mysqli_close($conexion);
}
?>
