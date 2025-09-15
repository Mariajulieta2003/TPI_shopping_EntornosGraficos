<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
    <?php
        include './layouts/Navbar.php';
    ?>

    <section class="container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-7 col-xl-6">
                <div class="card shadow-sm">
                    <div class="card-body p5">
                    <h1 class="h3 mb-4 text-center">Registrarse</h1>

                    <form action="ProcesarRegistroUS.php" method="POST" autocomplete="on">
                        <div class="form-floating mb-3">
                            <input type="email" name="email" id="email" class="form-control form-control-lg"
                                placeholder="x@xxx.com" autocomplete="username" required>
                            <label for="email" class="fs-6">Email</label>
                        </div>

                        <div class="form-floating mb-3">
                            <input type="password" name="password" id="password" class="form-control form-control-lg"
                            placeholder=" " autocomplete="current-password" required>
                            <label for="pasword" class="fs-6">Contraseña</label>
                        </div>

                        <!--a futuro se puede agregar otro campo al formulario para confirmar contraseña-->
                        <button type="submit" class="btn btn-primary btn-lg w-100">Registrarse</button>

                    </form>

                    <div class="text-center mt-3 small">
                        ¿Ya posees una cuenta?<a href="login.php">Inicia sesion</a>
                    </div>

                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php
        include './layouts/footer.php';
    ?>

    <script src="https://kit.fontawesome.com/accf4898f4.js" crossorigin="anonymous"></script>  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>