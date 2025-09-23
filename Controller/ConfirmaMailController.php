<?php
 include_once("../Model/ProcesarRegistroUS.php");
try {
$email=$_GET['mail'];
actualizarEstado($email);

header("Location: ../View/ConfirmarMail.php");
    exit;
	
} catch (Exception $e) {
    echo $e->getMessage();
	exit;
}
?>