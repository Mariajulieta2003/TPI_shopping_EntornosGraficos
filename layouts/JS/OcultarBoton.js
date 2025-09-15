(function () {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;
    if (typeof el.remove === 'function') el.remove();        // moderno
    else if (el.parentNode) el.parentNode.removeChild(el);   // fallback
  }
  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', run, { once: true });
  else
    run();
})();
