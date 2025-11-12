<?php
$host = "localhost"; // Siempre localhost para XAMPP
$usuario = "root";   // Usuario por defecto
$contrasenia = "";   // Por defecto en XAMPP no tiene contraseña
$base_datos = "sistema_validacion"; 

// Crear conexión
$conexion = mysqli_connect($host, $usuario, $contrasenia, $base_datos);

// Verificar conexión
if (!$conexion) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}

// Asegurar codificación UTF-8
mysqli_set_charset($conexion, "utf8mb4");


?>