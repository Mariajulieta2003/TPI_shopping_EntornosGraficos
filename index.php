<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="pagina que simula el funcionamiento de un shopping.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="./layouts/css/index.css">

<link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <title>Shopping</title>
</head>

<body>
<style>
  .hero{
  background: linear-gradient(180deg, #4A3BC7 0%, #2A2668 100%);
  padding: 64px 0;
}
.hero .hero-search .form-control{ border-top-left-radius: 999px; border-bottom-left-radius:999px; }
.hero .hero-search .btn{ border-top-right-radius: 999px; border-bottom-right-radius:999px; }
  </style>
<?php include __DIR__ . '/layouts/Navbar.php'; ?>

  <section class="hero text-white">
    <div class="container py-5">
      <h1 class="display-5 fw-bold mb-4">Descubrí las mejores<br>tiendas y ofertas</h1>
      <form class="input-group input-group-lg hero-search" role="search" action="/buscar" method="get">
        <input class="form-control" name="q" type="search" placeholder="Buscar tiendas">
        <button class="btn btn-primary" type="submit">Buscar</button>
      </form>
    </div>
  </section>
</header>

 <main class="container my-5">

  <h2 class="h4 mb-3">Promociones destacadas</h2>
  <div class="row g-3 ">
    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="promo-top position-relative">
          <span class="badge badge-discount position-absolute top-50 start-0 translate-middle-y ms-3">30% OFF</span>
        </div>
        <div class="card-body" >
          <h3 class="h6 mb-1">Moda Chic</h3>
          <p class="small text-secondary mb-0">Indumentaria y accesorios.</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="promo-top position-relative">
          <span class="badge badge-discount position-absolute top-50 start-0 translate-middle-y ms-3">30% OFF</span>
        </div>
        <div class="card-body">
          <h3 class="h6 mb-1">ElectroHogar</h3>
          <p class="small text-secondary mb-0">Electrónica seleccionada.</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="promo-top position-relative">
          <span class="badge badge-discount position-absolute top-50 start-0 translate-middle-y ms-3">25% OFF</span>
        </div>
        <div class="card-body">
          <h3 class="h6 mb-1">Calzado Urbano GENERICO</h3>
          <p class="small text-secondary mb-0">Calzado y urban style.</p>
        </div>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
      <div class="card shadow-sm h-100">
        <div class="promo-top position-relative">
          <span class="badge badge-discount position-absolute top-50 start-0 translate-middle-y ms-3">40% OFF</span>
        </div>
        <div class="card-body">
          <h3 class="h6 mb-1">Deportes</h3>
          <p class="small text-secondary mb-0">Indumentaria deportiva.</p>
        </div>
      </div>
    </div>
  </div>

  <h2 class="h4 my-4">Categorias</h2>
  <div class="row g-3 categorias">
    <div class="col-6 col-md-3">
      <a class="card category-card text-center text-decoration-none h-100" href="#">
        <div class="card-body py-4">
        
          <i class="fa-solid fa-shirt fs-2 d-block mb-2"></i>
          <span class="fw-medium text-body">Ropa</span>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a class="card category-card text-center text-decoration-none h-100" href="#">
        <div class="card-body py-4">
          <i class="bi bi-tv fs-2 d-block mb-2"></i>
          <span class="fw-medium text-body">Electrodomésticos</span>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a class="card category-card text-center text-decoration-none h-100" href="#">
        <div class="card-body py-4">
          <i class="bi bi-house-door fs-2 d-block mb-2"></i>
          <span class="fw-medium text-body">Hogar</span>
        </div>
      </a>
    </div>
    <div class="col-6 col-md-3">
      <a class="card category-card text-center text-decoration-none h-100" href="#">
        <div class="card-body py-4">
          
          <i class="fa-solid fa-medal fs-2 d-block mb-2"></i>
          <span class="fw-medium text-body">Deportes</span>
        </div>
      </a>
    </div>
  </div>


  <h2 class="h4 my-4">Eventos</h2>
  <div class="row g-3">
    <div class="col-lg-8">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="card shadow-sm h-100 card event-card shadow-sm">
            <div class="card-body">
              <h3 class="h6 mb-1 fw-bold">Venta especial de primavera <b>2025</b></h3>
              <p class="small text-secondary mb-0">Resumen breve del evento…</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card shadow-sm h-100 card event-card shadow-sm">
            <div class="card-body">
              <h3 class="h6 mb-1">nueva sala de cine en......</h3>
              <p class="small text-secondary mb-0">con el gran estreono de .............</p>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card card event-card shadow-sm shadow-sm h-100">
            <div class="card-body">
              <h3 class="h6 mb-1"><b>martes 16/9/25</b></h3>
              <p class="small text-secondary mb-0 fw-medium">se viene nuevo local de empandas</p>
            </div>
          </div>
        </div>
      </div>
    </div>
   
  </div>
 <div>
      <h2 class="h4">Tiendas</h2>
      <iframe src="https://www.google.com/maps/embed?pb=!1m17!1m12!1m3!1d3272.5384890332753!2d-57.98214252424715!3d-34.89293367285195!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m2!1m1!2zMzTCsDUzJzM0LjYiUyA1N8KwNTgnNDYuNCJX!5e0!3m2!1ses!2sar!4v1757898954412!5m2!1ses!2sar"  style="border:0; width:100%; height: auto;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

 <section id="cta-locatarios" class="py-5 text-center text-white position-relative" style="background-image: url('ruta/a/tu/imagen.jpg'); background-size: cover; background-position: center; transition: background-size 0.3s ease;">
  <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark" style="opacity: 0.05;"></div>

  <div class="container position-relative z-index-1">
    <div class="row justify-content-center">
      <div class="col-lg-8">
        <h1 class="display-5 fw-bold text-dark">
          Abrí tu local en <?= htmlspecialchars($SHOPPING_NAME ?? 'nuestro shopping', ENT_QUOTES, 'UTF-8'); ?>
        </h1>
        <p class="lead mb-4 text-dark">
          Alto flujo de visitas. Contratos flexibles. Ubicaciones premium.
        </p>

        <div class="d-flex flex-column flex-sm-row justify-content-center gap-2">
          <a class="btn btn-primary btn-lg"
             href="./View/CrearTienda.php"
             aria-label="Ir al formulario para abrir un local">
            Quiero abrir un local
          </a>

          <a class="btn btn-outline-dark btn-lg"
             href="/docs/dossier-comercial.pdf"
             aria-label="Descargar dossier comercial para locatarios">
            Descargar dossier
          </a>
        </div>

        <div class="mt-3 small text-muted">
          Espacios desde XX m². Respuesta en 24 h.
        </div>
      </div>
    </div>
  </div>
</section>>
</main>

<?php include __DIR__ . '/layouts/footer.php'; ?>

<script src="https://kit.fontawesome.com/accf4898f4.js" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

</body>
</html>