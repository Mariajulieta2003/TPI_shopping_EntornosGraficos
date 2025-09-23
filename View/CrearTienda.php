<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="pagina que simula el funcionamiento de un shopping.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="/layouts/css/index.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
<style>
    .btn-primary.btn-lg {
        transition: background 0.3s, transform 0.2s, box-shadow 0.2s;
    }
    .btn-primary.btn-lg:hover, .btn-primary.btn-lg:focus {
        background: linear-gradient(90deg, #0d6efd 60%, #4f8cff 100%);
        transform: translateY(-2px) scale(1.04);
        box-shadow: 0 6px 24px rgba(13,110,253,0.18);
    }
</style>
    <title>Crear nueva tienda</title>
</head>
<body>
        <?php  include 'C:\xampp\htdocs\TPIShopping\layouts\Navbar.php'; ?>

        <div class="container my-5">
                    <div class="row justify-content-center">
                        <div class="col-12 col-lg-10 col-xl-9">
                            <div class="card shadow-lg border-0 rounded-4" style="max-width: 100%;">
                        <div class="card-body p-4">
                            <h2 class="mb-4 text-center">Solicitud de apertura de tienda</h2>
                            <form method="post" action="#">
                                <h5 class="mb-3">Datos personales</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="apellido" class="form-label">Apellido</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="password" class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="telefono" class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="telefono" name="telefono" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="sexo" class="form-label">Sexo</label>
                                        <select class="form-select" id="sexo" name="sexo" required>
                                            <option value="" selected disabled>Seleccione</option>
                                            <option value="Femenino">Femenino</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Otro">Otro</option>
                                        
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="dni" class="form-label">DNI</label>
                                        <input type="text" class="form-control" id="dni" name="dni" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="cuil" class="form-label">CUIL</label>
                                        <input type="text" class="form-control" id="cuil" name="cuil" required>
                                    </div>
                                </div>
                                <hr class="my-4">
                                <h5 class="mb-3">Datos de la tienda</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="rubro" class="form-label">Rubro</label>
                                        <input type="text" class="form-control" id="rubro" name="rubro" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="nombre_local" class="form-label">Nombre del local</label>
                                        <input type="text" class="form-control" id="nombre_local" name="nombre_local" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="lugar" class="form-label">Lugar disponible en el shopping</label>
                                        <select class="form-select" id="lugar" name="lugar" required>
                                            <option value="" selected disabled>Seleccione un lugar</option>
                                            <option value="Local 1">Local 1 - Planta Baja</option>
                                            <option value="Local 2">Local 2 - Planta Baja</option>
                                            <option value="Local 3">Local 3 - Planta Alta</option>
                                            <option value="Local 4">Local 4 - Planta Alta</option>
                                            <option value="Isla 1">Isla 1 - Centro</option>
                                            <option value="Isla 2">Isla 2 - Centro</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">Solicitar apertura</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'C:\xampp\htdocs\TPIShopping\layouts\footer.php'; ?>
</body>
</html>