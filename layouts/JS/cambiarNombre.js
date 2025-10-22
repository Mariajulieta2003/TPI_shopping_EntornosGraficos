(function () {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;

    // Redirige a la raíz del sitio
    el.setAttribute('href', '/TPIShopping/View/DashBoardCliente.php');

    // Cambia el texto visible a "dashboard" preservando un posible <i>
    const icon = el.querySelector('i');
    if (icon) {
      el.innerHTML = icon.outerHTML + ' Dashboard';
    } else {
      el.textContent = 'dashboard';
    }

    // (Opcional) actualiza el id
    el.id = 'btnDashboard';

    // Fallback si no fuera un <a>
    if (el.tagName.toLowerCase() !== 'a') {
      el.addEventListener('click', (e) => {
        e.preventDefault();
        location.assign('/TPIShopping/');
      });
    }
  }

  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', run, { once: true });
  else
    run();
})();
