<?php
include("conexion.php"); // Conexión a MySQL

// Verificamos si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturamos el valor del input llamado "cuil"
    $cuil = $_POST["cuil"];

    // Preparamos la consulta (buscamos al alumno por su CUIL)
    $consulta = "SELECT * FROM alumno WHERE ID_Cuil_Alumno = '$cuil'";
    $resultado = mysqli_query($conexion, $consulta);

    // Verificamos si hay resultados
    if (mysqli_num_rows($resultado) > 0) {
        // Extraemos los datos
        $alumno = mysqli_fetch_assoc($resultado);
        echo "<h2>Datos del alumno:</h2>";
        echo "Nombre: " . $alumno["Nombre_Alumno"] . "<br>";
        echo "Apellido: " . $alumno["Apellido_Alumno"] . "<br>";
        echo "Email: " . $alumno["Email_Alumno"] . "<br>";
    } else {
        echo "<p>No se encontró ningún alumno con ese CUIL.</p>";
    }
}
?>
<?php
// Cerrar conexión
mysqli_close($conexion);
?>