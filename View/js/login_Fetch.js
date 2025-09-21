document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('form');  
    const msg = document.getElementById('formMsg'); 
    const btn = document.querySelector('button[type="submit"]');  

  
    const showToast = (type, text) => {
        Toastify({
            text: text,
            duration: 3000, 
            close: true, 
            gravity: "top",  
            position: "right",
            backgroundColor: type === 'success' ? "green" : "red",  
        }).showToast();
    };

  
    form.addEventListener('submit', async (e) => {
        e.preventDefault();  

        btn.disabled = true; 

        const formData = new FormData(form);  

        try {
           
            const res = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const data = await res.json();  

            if (data.ok) {
                showToast('success', data.message || '¡Inicio de sesión exitoso!');
                if (data.redirect) {
                    window.location.href = data.redirect; 
                }
            } else {
                showToast('error', data.message || 'Email o contraseña incorrectos.');
            }
        } catch (err) {
            showToast('error', 'Fallo en la conexión. Intenta nuevamente.');
        } finally {
            btn.disabled = false; 
        }
    });
});
