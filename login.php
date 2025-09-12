<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="layouts/css/EstiloLogin.css" rel="stylesheet">
</head>
<body>
    <!-- Este login es un mero prototipo para que puedan testear el inicio de sesion y/o el registro-->
    <?php
      //se incluye el navbar prototipo a modo de referencia
     include 'layouts//Navbar.php'
    ?>
    
      <div class="modal" id="MODAL" tabindex="-1" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header" id="cabecera">
              <h5 class="modal-title">Login</h5>
            </div>
            <div class="modal-body">
              <form action="ProcesarLogin.php" method="post">
                  <div class="form-floating mb-3">
                      <input type="email" placeholder="Email" name="mail" class="form-control">
                      <label for="mail">Email</label>
                  </div>

                  <div class="form-floating mb-3">
                      <input type="password" placeholder="Contraseña" name="password" class="form-control">
                      <label for="password">Contraseña</label>
                  </div>

                  <button type="submit" value=" inciar sesion" name="enviar" class="btn btn-primary mb-3">iniciar sesion</button>

              </form>

              <br/>
              <a href="algo">Olvidaste tu contraseña?</a>
              <br/>
              <a href="otroalgo">No tienes usuario?Registrate</a>

              </form>
            </div>
          </div>
        </div>
      </div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>