<?php
    include_once 'funciones.php';

    $mail=trim($_POST["mail"]);
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

        print_r($cant_coincidencias);

        if(!($cant_coincidencias["cant"]>0)){
            //tipoFK='1' asumiendo que ese es el ID del tipo cliente
            //tipoFK='1' asumiendo que ese es el ID la categoria mas baja
            $consul_insercion="INSERT into usuario (IDusuario,nombreUsuario,clave,tipoFK,categoriaFK)
                                VALUES ('','".$mail."','".$password."','1','1')";

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
    //header("location: login.php");
    echo "sas";
    exit();

?>