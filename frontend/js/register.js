// cargar los roles disponibles desde el servidor
async function cargarRoles() {
    const select = document.getElementById('reg-rol');
    try {
        const res = await fetch('./backend/get_roles.php');
        const datos = await res.json();
        if (datos.success) {
            // limpiar y volver a crear opciones
            select.innerHTML = '<option value="" disabled selected>Selecciona un Rol</option>';
            datos.roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.nombre;
                opt.textContent = r.nombre.charAt(0).toUpperCase() + r.nombre.slice(1);
                select.appendChild(opt);
            });
        } else {
            console.error('No se pudieron cargar los roles:', datos.message);
        }
    } catch (err) {
        console.error('Error al obtener roles', err);
    }
}

document.addEventListener('DOMContentLoaded', cargarRoles);

// Manejar el envío del formulario de registro
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const username = document.getElementById('reg-username').value;
    const password = document.getElementById('reg-password').value;
    const rol = document.getElementById('reg-rol').value;
    
    try {
        const respuesta = await fetch('./backend/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ username, password, rol })
        });
        
        const datos = await respuesta.json();
        
        if (datos.success) {
            alert('Usuario registrado exitosamente. Por favor inicia sesión');
            // Redirigir al login
            window.location.href = './index.html';
        } else {
            alert(datos.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error en la conexión con el servidor');
    }
});
