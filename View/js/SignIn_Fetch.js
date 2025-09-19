document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('regForm');
  const msg  = document.getElementById('formMsg');
  const btn  = document.getElementById('btnEnviar');

  const show = (type, text) => {
    if (!msg) return;
    msg.innerHTML = `<div class="alert alert-${type} py-2 mb-0">${text}</div>`;
  };

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

  
    const ac = new AbortController();
    const to = setTimeout(() => ac.abort(), 15000); // Establecer un límite de 15 segundos para la petición

    btn.disabled = true;

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),            
        headers: { 'Accept': 'application/json' },
        cache: 'no-store',
        signal: ac.signal 
      });

      const ct   = res.headers.get('content-type') || '';
      const data = ct.includes('application/json')
        ? await res.json()
        : { ok: res.ok, message: await res.text() };

      if (!res.ok || data.ok === false) {
        const err = data.errors?.join('<br>') || data.message || 'Error';
        show('danger', err);
        return;
      }

      show('success', data.message || 'OK');
      if (data.redirect) window.location.href = data.redirect;

    } catch (err) {
      // Capturar errores, como fallo en la red o cancelación de la solicitud
      show('danger', 'Fallo de red o petición cancelada.');
    } finally {
      clearTimeout(to); // Limpiar el timeout cuando termina la solicitud
      btn.disabled = false; // Volver a habilitar el botón
    }
  });
});
