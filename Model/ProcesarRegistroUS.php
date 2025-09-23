<?php
   /* include_once 'funciones.php';

    $mail=trim($_POST["email"]);
    echo $mail;
    $password=trim($_POST["password"]);
    echo $password;
    

    if(!empty($mail) && !empty($password)){
        //$asunto="Codigo de confirmacion";
        
        //$cuerpo="Su codigo de confirmacion es:");

        //solo a nivel basico, reemplazar con phpMailer o cualquier otro tipo libreria para el envio de mails
        //mail($mail,$asunto,$cuerpo)

        $consult_verificacion="SELECT count(U.IDusuario) as cant
                                FROM USUARIO U
                                WHERE U.nombreUsuario = '".$mail."'";
        
        $cant_coincidencias=slql_consul($consult_verificacion);

        if(!($cant_coincidencias["cant"]>0)){
            //tipoFK='1' asumiendo que ese es el ID del tipo cliente
            //tipoFK='1' asumiendo que ese es el ID la categoria mas baja
            $consul_insercion="INSERT into usuario (IDusuario,nombreUsuario,clave,tipoFK,categoriaFK,estado)
                                VALUES ('','".$mail."','".$password."','1','1','1')";

            $result=slql_consul($consul_insercion);

            if($result){
                //la insercion se realizo correctamente
                header("location: index.php");
                exit();
            } else {
                //no se pudo realizar consulta
                header("location: login.php");
                exit();
            };

        }

    }
    //La constraseña o el mail estaban vacios
    header("location: registro.php");
    exit();*/
include_once("../Model/conexion.php");


function ExisteUsuario($username, $email) {
    $pdo = getConnection();

    $sql = "SELECT COUNT(*) FROM usuario WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'email' => $email
    ]);
   
    $count = $stmt->fetchColumn();
    return $count > 0; 
}

function EstaActivo($email):bool
{
$pdo = getConnection();
$sql = 'select usuario.estado from usuario where email= :email';
$stmt = $pdo->prepare($sql);
$stmt->execute(["email"=> $email]);

$Existencia = $stmt->fetchColumn();
return $Existencia == 0;
}

function insertarUsuario( $nombre, $email, $pwd, $tel, $sexo, $dni) {

    EstaActivo($email)? true : throw new Exception("mail ya verificado,verifique el mail");

    $tipoFK=1 ;
    $categoriaFK=1; 
    $estado=0;

    $pdo = getConnection();
    $sql = "INSERT INTO `usuario`
              (`nombreUsuario`,`email`,`clave`,`telefono`,`Sexo`,`tipoFK`,`categoriaFK`,`estado`,`DNI`)
            VALUES
              (:nombre, :email, :clave, :telefono, :sexo, :tipoFK, :categoriaFK, :estado,:DNI)";
    $stmt = $pdo->prepare($sql);
    $ok = $stmt->execute([
        ':nombre'      => $nombre,
        ':email'       => $email,
        ':clave'       => password_hash($pwd, PASSWORD_DEFAULT),
        ':telefono'    => $tel,
        ':sexo'        => $sexo,          
        ':tipoFK'      => (int)$tipoFK,   
        ':categoriaFK' => (int)$categoriaFK, 
        ':estado'      => (int)$estado ,
        ':DNI'      => $dni,
    ]);
    return $ok ? (int)$pdo->lastInsertId() : false;
}



?>