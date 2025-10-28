<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

echo "📨 Script enviar.php cargado correctamente.<br>";
var_dump($_POST);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $mensaje = $_POST['mensaje'];

    $mail = new PHPMailer(true);

    
    try {
        // Configuración del servidor SMTP (por ejemplo, Gmail)
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sollione2004@gmail.com'; // tu correo
        $mail->Password   = 'masu hqty zqfc pudz'; // NO la contraseña normal
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Remitente y destinatario
        $mail->setFrom('sollione2004@gmail.com', 'Sistema de Validacion UTN FRH'); //mail que manda el msj
        $mail->addAddress('spuello646@alumnos.frh.utn.edu.ar'); // extension@frh.utn.edu.ar

        // Contenido del mail
        $mail->isHTML(true);
        $mail->Subject = 'Mensaje de formulario de contacto';
        $mail->Body = "
            <h3>Datos del visitante:</h3>
            <p><b>Nombre:</b> $nombre $apellido</p>
            <p><b>Email:</b> $email</p>
            <p><b>Mensaje:</b><br>$mensaje</p>
        ";


        $mail->send();
        echo '✅ El mensaje fue enviado correctamente.';
    } catch (Exception $e) {
        echo "❌ Error al enviar el mensaje: {$mail->ErrorInfo}";
    }
}
?>
