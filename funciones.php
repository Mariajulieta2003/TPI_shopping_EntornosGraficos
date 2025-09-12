<?php
    function slql_consul($consulta){
        
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
?>