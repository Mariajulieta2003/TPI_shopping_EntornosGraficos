<?php
    if(isset($_SESSION["MensajeError1"])){
        ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                Email o Contraseña invalidos.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php
        unset($_SESSION["MensajeError1"]);
    } 
?>
