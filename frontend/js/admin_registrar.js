// cargar roles dinámicamente y enviar formulario para crear usuario (misma lógica que register.js)

async function cargarRolesAdmin() {
    const select = document.getElementById('admin-rol');
    try {
        const res = await fetch('./backend/get_roles.php');
        const datos = await res.json();
        if (datos.success) {
            select.innerHTML = '<option value="" disabled selected>Selecciona un Rol</option>';
            datos.roles.forEach(r => {
                const opt = document.createElement('option');
                opt.value = r.nombre;
                opt.textContent = r.nombre.charAt(0).toUpperCase() + r.nombre.slice(1);
                select.appendChild(opt);
            });
        } else {
            console.error('Error cargando roles:', datos.message);
        }
    } catch (err) {
        console.error('Error de red al obtener roles', err);
    }
}

async function enviarFormularioAdmin(e) {
    e.preventDefault();
    const nombre = document.getElementById('admin-username').value;
    const password = document.getElementById('admin-password').value;
    const rol = document.getElementById('admin-rol').value;

    try {
        const respuesta = await fetch('./backend/register.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username: nombre, password, rol })
        });
        const datos = await respuesta.json();
        if (datos.success) {
            alert('Usuario creado con éxito');
            // después de crear, volver a la lista o limpiar el formulario
            window.location.href = 'admin_usuarios.php';
        } else {
            alert(datos.message);
        }
    } catch (err) {
        console.error('Error al crear usuario', err);
        alert('Ocurrió un error en la comunicación con el servidor');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    cargarRolesAdmin();
    document.getElementById('adminRegisterForm').addEventListener('submit', enviarFormularioAdmin);
});