<?php
/*$to = "sitare8285@dotxan.com";  // Dirección de destino
$subject = "Asunto del mensaje";   // Asunto
$message = "Este es el cuerpo del mensaje."; // Cuerpo del mensaje
$headers = "From: labarbahipolito3@gmail.com" . "\r\n" .
           "Reply-To: labarbahipolito3@gmail.com" . "\r\n" ; // Cabeceras del correo*/

function EnviaMail($to, $subject, $message)
{
    $headers = "From: labarbahipolito3@gmail.com" . "\r\n" .
        "Reply-To: labarbahipolito3@gmail.com" . "\r\n";
    try {
        if (mail($to, $subject, $message, $headers)) {
            echo "Correo enviado correctamente.";
        } else {
            echo "Hubo un error al enviar el correo.";
        }
    } catch (\Exception $e) {
        echo $e->getTrace();
    }
}
