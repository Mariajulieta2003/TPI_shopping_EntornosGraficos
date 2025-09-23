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

    // Configuración para cancelar la solicitud si tarda demasiado
    const ac = new AbortController();
    const to = setTimeout(() => ac.abort(), 15000);  // Timeout de 15 segundos

    // Deshabilitar el botón para evitar múltiples envíos
    btn.disabled = true;

    try {
      // Enviar el formulario usando fetch
      const res = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),            
        headers: { 'Accept': 'application/json' },
        cache: 'no-store',
        signal: ac.signal  // Añadir la señal para abortar si excede el tiempo
      });

      // Verificar si la respuesta es JSON
      const ct = res.headers.get('content-type') || '';
      const data = ct.includes('application/json')
        ? await res.json()  // Parsear la respuesta como JSON
        : { ok: res.ok, message: await res.text() };  // En caso de no ser JSON

      // Si la respuesta del servidor indica error, mostrar el mensaje de error
      if (!res.ok || data.ok === false) {
        const err = data.message || 'Ocurrió un error en el servidor';
        show('danger', err);  // Mostrar mensaje de error
        return;  // Detener la ejecución si hay error
      }

      // Si todo está OK, mostrar el mensaje de éxito
      show('success', data.message || '¡Datos validados correctamente!');

      // Si hay una URL de redirección, redirigir al usuario
      if (data.redirect) {
        window.location.href = data.redirect;  // Redirigir a la URL proporcionada
      }

    } catch (err) {
      // Capturar errores de red o problemas con la solicitud
      show('danger', data);
    } finally {
      // Limpiar el timeout y volver a habilitar el botón
      clearTimeout(to); 
      btn.disabled = false; 
    }
  });
});
