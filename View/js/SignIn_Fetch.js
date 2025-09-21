document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('regForm');
  const msg  = document.getElementById('formMsg');
  const btn  = document.getElementById('btnEnviar');

 if (!res.ok || data.ok === false) {
    // Muestra mensaje de error 
    const err = data.message || 'Ocurrió un error';
    show('danger', err);
    return;
}

//todo OK
show('success', data.message || 'Todo salió bien');

if (data.redirect) window.location.href = data.redirect;


  form.addEventListener('submit', async (e) => {
    e.preventDefault();

  
    const ac = new AbortController();
    const to = setTimeout(() => ac.abort(), 15000);

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
      // Capturar errores
      show('danger', 'Fallo de red o petición cancelada.');
    } finally {
      clearTimeout(to); 
      btn.disabled = false; 
    }
  });
});
