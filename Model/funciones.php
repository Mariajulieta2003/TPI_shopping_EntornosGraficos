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
        
    }*/


        $host = '127.0.0.1';
        $user = 'root';
        $pass = 'root';
        $dbname = 'shopping';
        $conn = new mysqli($host, $user, $pass, $dbname);

     if(!$conn) {
        die('Error de conexión: '. mysqli_connect_error());
     }else{echo "Conexión exitosa a la base de datos";}