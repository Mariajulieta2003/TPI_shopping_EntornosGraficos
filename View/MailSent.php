<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mail enviado</title>
  <link rel="stylesheet" href="./css/MailSent.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <style>
    body {
      background:  #F3F1FF;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .mail-card {
      background: #fff;
      border-radius: 1rem;
      box-shadow: 0 0.5rem 1.5rem rgba(74,59,199,0.08);
      padding: 2.5rem 2rem;
      max-width: 400px;
      width: 100%;
      text-align: center;
    }
    .mail-icon {
      font-size: 4.5rem;
      color: var(--primary, #4A3BC7);
      margin-bottom: 1rem;
    }
    .btn-primary {
      background-color: #4A3BC7;
      border-color: #4A3BC7;
    }
    :root {
      --primary: #4A3BC7;
      --subtle: #F3F1FF;
    }
  </style>
</head>
<body>
  <div class="mail-card mx-auto" style="max-width: 520px; padding: 3.5rem 2.5rem;">
    <i class="bi bi-envelope-check mail-icon" style="font-size:6rem;"></i>
    <h1 class="display-5 mb-4" style="color:#4A3BC7; font-weight:700;">¡Correo enviado!</h1>
    <p class="fs-5 mb-5 text-secondary">Te enviamos un mail a tu casilla.<br>Por favor, revisa tu bandeja de entrada y confirma tu dirección para continuar.</p>
    <div class="mb-2">
      <span class="fw-bold text-dark fs-5">Ir a verificar:</span>
    </div>
    <div class="d-flex flex-column flex-md-row justify-content-center gap-3 mb-4">
      <a href="https://mail.google.com" target="_blank" class="btn btn-light border d-flex align-items-center gap-2 px-4 py-2 shadow-sm" style="font-size:1.3rem;">
        <img src="https://upload.wikimedia.org/wikipedia/commons/4/4e/Gmail_Icon.png" alt="Gmail" style="width:2rem; height:2rem;"> Gmail
      </a>
      <a href="https://outlook.live.com" target="_blank" class="btn btn-light border d-flex align-items-center gap-2 px-4 py-2 shadow-sm" style="font-size:1.3rem;">
        <span style="width:2rem; height:2rem; display:inline-block;">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32">
            <rect width="32" height="32" rx="6" fill="#0072C6"/>
            <path d="M8 10h16v12H8z" fill="#fff"/>
            <path d="M8 10l8 6 8-6" fill="none" stroke="#0072C6" stroke-width="2"/>
          </svg>
        </span> Outlook
      </a>
      <a href="https://mail.yahoo.com" target="_blank" class="btn btn-light border d-flex align-items-center gap-2 px-4 py-2 shadow-sm" style="font-size:1.3rem;">
        <span style="width:2rem; height:2rem; display:inline-block;">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32">
            <rect width="32" height="32" rx="6" fill="#6001D2"/>
            <text x="16" y="22" text-anchor="middle" font-size="16" fill="#fff" font-family="Arial, sans-serif">Y!</text>
          </svg>
        </span> Yahoo
      </a>
    </div>
    <a href="../index.php" class="btn btn-primary btn-lg">Volver al inicio</a>
  </div>





  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Toasty JS -->
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
    <div id="toastCuentaCreada" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body">
          <i class="bi bi-person-check-fill me-2"></i>
          ¡Cuenta creada exitosamente!
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
      </div>
    </div>
  </div>
  <script>
    window.addEventListener('DOMContentLoaded', function() {
      var toastEl = document.getElementById('toastCuentaCreada');
      var toast = new bootstrap.Toast(toastEl, { delay: 3500 });
      toast.show();
    });
  </script>
</body>
</html>
