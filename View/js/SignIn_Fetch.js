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
    const to = setTimeout(() => ac.abort(), 15000); // 15 segundos

   
    btn.disabled = true;

    try {
      // Enviar el formulario usando fetch
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),            
        headers: { 'Accept': 'application/json' },
        cache: 'no-store',
        signal: ac.signal 
      });

     
      const ct = res.headers.get('content-type') || '';
      const data = ct.includes('application/json')
        ? await res.json()  
        : { ok: res.ok, message: await res.text() };  

 
      if (!res.ok || data.ok === false) {
        const err = data.message || 'Ocurrió un error en el servidor';
        show('danger', err);  
        return; 
      }

    
      show('success', data.message || '¡Datos validados correctamente!');

      if (data.redirect) {
        window.location.href = data.redirect;  // Redirigir a URL 
      }

    } catch (err) {
   
      show('danger', data);
    } finally {
    
      clearTimeout(to); 
      btn.disabled = false; 
    }
  });
});
