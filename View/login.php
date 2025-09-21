<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="./css/EstiloLogin.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

    
<link href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css" rel="stylesheet">



  </head>
<body>
    <!-- Este login es un mero prototipo para que puedan testear el inicio de sesion y/o el registro-->

  <?php 
    include '../layouts/Navbar.php';
  
  ?>

     

  <section class="container py-5">
  <div class="row justify-content-center">
    <!-- más ancho en desktop -->
    <div class="col-12 col-md-8 col-lg-7 col-xl-6">
      <div class="card shadow-sm">
        <div class="card-body p-5">
          <h1 class="h3 mb-4 text-center">Iniciar sesión</h1>

      

          <form action="../Controller/LogInController.php" method="post" autocomplete="on">
            <div class="form-floating mb-3">
              <input id="email" name="mail" type="email"
                     class="form-control form-control-lg" placeholder=" "
                     autocomplete="username" required>
              <label for="email" class="fs-6">Email</label>
            </div>

            <div class="form-floating mb-3">
              <input id="password" name="password" type="password"
                     class="form-control form-control-lg" placeholder=" "
                     autocomplete="current-password" required>
              <label for="password" class="fs-6">Contraseña</label>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1">
                <label class="form-check-label" for="remember">Recordarme</label>
              </div>
              <a class="small" href="recuperar.php">¿Olvidaste tu contraseña?</a>
            </div>

            <!-- botón grande y a todo el ancho -->
            <button type="submit" class="btn btn-primary btn-lg w-100">Ingresar</button>
          </form>

          <div class="text-center mt-3 small">
            ¿No tenés cuenta? <a href="../View/signIn.php">Registrate</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
  <?php include '../layouts/footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.js"></script>

  <script src="./js/login_Fetch.js"> </script>
<script src="https://kit.fontawesome.com/accf4898f4.js" crossorigin="anonymous"></script>  
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script><script src="../layouts/JS/OcultarBoton.js" ></script>

</body>
</html>