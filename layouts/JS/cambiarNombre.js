(function () {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;

    el.setAttribute('href', '/TPIShopping/View/DashBoardCliente.php');

    const icon = el.querySelector('i');
    if (icon) {
      el.innerHTML = icon.outerHTML + ' Dashboard';
    } else {
      el.textContent = 'dashboard';
    }

    el.id = 'btnDashboard';

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
