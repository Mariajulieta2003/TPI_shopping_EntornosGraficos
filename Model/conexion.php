<?php
  /*function slql_consul($consulta){
        
        $conection=mysqli_connect("127.0.0.1","root","") or die("error de coneccion: ". mysqli_connect_error());
        
        mysqli_select_db($conection,"shopping");

        $resul=mysqli_query($conection,$consulta) or die("error en consulta: ". mysqli_error($conection));

        //Verificamos el resultado antes de transformarlo en array asociativo
        if(!is_bool($resul)){
            $resul_arr=mysqli_fetch_assoc($resul);
        }
        
        
        mysqli_close($conection);

        return $resul_arr;  
        
    }


        */


function getConnection(): mysqli {
    $host = "127.0.0.1";
    $user = "root";
    $pass = "root";
    $db   = "shopping";

    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_errno) {
        
        error_log("Error de conexión a BD: " . $conn->connect_error);
        echo "No se pudo conectar a la base de datos. Intenta más tarde.";
        exit;
    }

    $conn->set_charset("utf8mb4"); // para acentos y ñ
    return $conn;
}