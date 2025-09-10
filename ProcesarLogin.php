<?php
    include_once ("funciones.php");
    session_start();

    $email=trim($_POST["mail"]);
    $password=trim($_POST["password"]);

    if(!empty($email) && !empty($password)){
        
            $consulta="SELECT * from usuario
                        where nombreUsuario='".$email."'
                         AND clave='".$password."'";

            $result=slql_consul($consulta);

            if(!empty($result)){
                $_SESSION["id"]=$result["IDusuario"];
                //reemplazar por destino final luego del login
                header("location: Perfil.php");
                exit();
            }

            //retorna a login para que inicie sesion nuevamente
            header("location: login.php");
            exit();
            
        }

    

?>