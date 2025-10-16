<?php
// tiendas.php — listado de tiendas con modal de detalle
include("../Model/ListadoTienda.php");

/// Traer tiendas
$tiendas = listarTiendas(200);

// Helper de salida segura
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="es" data-bs-theme="light">
<head>
  <meta charset="utf-8">
  <title>Tiendas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap + Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

  <!-- CSS mínimo (paleta + toques) -->
  <style>
    :root{ --bs-primary:#4A3BC7; }
    .hero{ background: linear-gradient(180deg,#F3F1FF 0%, #fff 70%); } /* suaviza cortes */
    .tile{ border:1px solid rgba(74,59,199,.10); }
    .tile:hover{ box-shadow:0 12px 36px rgba(74,59,199,.12); }
    /* Si tu navbar es fixed-top, descomenta la línea de abajo */
    /* body{ padding-top:72px; } */
  </style>
</head>
<body>

  <?php include_once(__DIR__ . "/../layouts/Navbar.php"); ?>

  <!-- HERO compacto -->
  <section class="hero border-bottom py-3 py-md-4">
    <div class="container-xxl">
      <h1 class="h3 text-primary mb-1">Tiendas</h1>
      <p class="text-body-secondary mb-0">Listado de locales y su ubicación dentro del shopping.</p>
    </div>
  </section>

  <!-- CONTENIDO -->
  <main class="container-xxl py-4 py-md-5">

    <?php if (empty($tiendas)): ?>
      <div class="alert alert-light border text-body-secondary">No hay tiendas para mostrar.</div>
    <?php else: ?>
      <div class="row row-cols-1 gy-3 gy-md-4">
        <?php foreach ($tiendas as $t): ?>
          <?php
            $localNombre = $t['local_nombre'] ?? '';
            $localRubro  = $t['local_rubro']  ?? '';
            $ubiNombre   = $t['ubicacion_nombre'] ?? '—';
            $ubiDesc     = $t['ubicacion_descripcion'] ?? '—';
          ?>
          <div class="col">
            <article class="card tile rounded-4 shadow-sm border-0">
              <div class="card-body p-3 p-md-4 d-flex flex-column flex-lg-row align-items-lg-center gap-3">
                <div class="flex-grow-1">
                  <h2 class="h6 mb-2 fw-semibold"><?= h($localNombre) ?></h2>

                  <ul class="list-inline text-body-secondary small mb-0 d-flex flex-wrap gap-3">
                    <li class="list-inline-item" title="Rubro">
                      <i class="bi bi-tag me-1"></i><?= h($localRubro ?: '—') ?>
                    </li>
                    <li class="list-inline-item" title="Ubicación">
                      <i class="bi bi-geo-alt me-1"></i><strong><?= h($ubiNombre) ?></strong>
                    </li>
                    <li class="list-inline-item d-none d-md-inline" title="Descripción de la ubicación">
                      <i class="bi bi-info-circle me-1"></i><?= h(mb_strimwidth($ubiDesc, 0, 100, '…', 'UTF-8')) ?>
                    </li>
                  </ul>
                </div>

                <div class="ms-lg-auto">
                  <button
                    class="btn btn-outline-primary rounded-pill px-3"
                    data-bs-toggle="modal" data-bs-target="#tiendaModal"
                    data-nombre="<?= h($localNombre) ?>"
                    data-rubro="<?= h($localRubro ?: '—') ?>"
                    data-ubicacion="<?= h($ubiNombre) ?>"
                    data-ubicaciondesc="<?= h($ubiDesc) ?>"
                  >Ver detalles</button>
                </div>
              </div>
            </article>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </main>

  <!-- MODAL: detalle -->
  <div class="modal fade" id="tiendaModal" tabindex="-1" aria-labelledby="tiendaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content rounded-4">
        <div class="modal-header bg-primary text-white rounded-top-4">
          <h5 class="modal-title" id="tiendaModalLabel"><i class="bi bi-shop me-2"></i>Detalle de tienda</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <p class="mb-1"><strong id="mNombre">—</strong></p>
          <p class="mb-1"><strong>Rubro:</strong> <span id="mRubro">—</span></p>
          <hr>
          <p class="mb-1"><strong>Ubicación:</strong> <span id="mUbicacion">—</span></p>
          <p class="mb-0"><strong>Descripción:</strong> <span id="mUbicacionDesc">—</span></p>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
  </div>

  <?php include_once(__DIR__ . "/../layouts/footer.php"); ?>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Poblar modal con los data-* del botón
    const tiendaModal = document.getElementById('tiendaModal');
    const mNombre = document.getElementById('mNombre');
    const mRubro = document.getElementById('mRubro');
    const mUbicacion = document.getElementById('mUbicacion');
    const mUbicacionDesc = document.getElementById('mUbicacionDesc');

    tiendaModal.addEventListener('show.bs.modal', (ev) => {
      const btn = ev.relatedTarget;
      const get = (a) => btn?.getAttribute(a) || '';

      mNombre.textContent        = get('data-nombre');
      mRubro.textContent         = get('data-rubro');
      mUbicacion.textContent     = get('data-ubicacion');
      mUbicacionDesc.textContent = get('data-ubicaciondesc');
    });
  </script>
</body>
</html>