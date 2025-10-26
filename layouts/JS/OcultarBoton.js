(function () {
  function run() {
    const el = document.getElementById('btnLogin');
    if (!el) return;
    if (typeof el.remove === 'function') el.remove();        
    else if (el.parentNode) el.parentNode.removeChild(el);   
  }
  if (document.readyState === 'loading')
    document.addEventListener('DOMContentLoaded', run, { once: true });
  else
    run();
})();

