<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cuenta habilitada</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <style>
    body {
      background: linear-gradient(135deg, #9a95caff 0%, #7784e9ff 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .card {
      border: none;
      border-radius: 1.5rem;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
      animation: fadeInDown 1s;
    }
    @keyframes fadeInDown {
      from { opacity: 0; transform: translateY(-40px); }
      to { opacity: 1; transform: translateY(0); }
    }
    .icon-success {
      font-size: 4rem;
      color: #198754;
      animation: bounce 1.2s;
    }
    @keyframes bounce {
      0%, 100% { transform: scale(1); }
      50% { transform: scale(1.2); }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-5">
        <div class="card p-4 text-center">
          <div class="mb-3">
            <i class="fa-solid fa-circle-check icon-success"></i>
          </div>
          <h2 class="mb-2">¡Cuenta habilitada!</h2>
          <p class="mb-4">Tu cuenta ha sido activada de forma satisfactoria.<br>Ya puedes comenzar a navegar y disfrutar de todas las funcionalidades de <b>SHOPPING GENERICO</b>.</p>
          <a href="../index.php" class="btn btn-success btn-lg px-4 shadow-sm">
            <i class="fa-solid fa-shop me-2"></i>Ir a la tienda
          </a>
        </div>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
