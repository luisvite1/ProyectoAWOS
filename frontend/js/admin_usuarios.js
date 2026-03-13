// cuando el DOM esté listo, solicitar la lista de usuarios
document.addEventListener('DOMContentLoaded', async () => {
    const tbody = document.querySelector('.tabla tbody');

    try {
        const res = await fetch('./backend/get_users.php');
        const data = await res.json();
        if (data.success) {
            tbody.innerHTML = '';
            data.users.forEach(user => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${user.id}</td>
                    <td>${user.username}</td>
                    <td>${user.rol_nombre}</td>
                    <td class="${user.activo ? 'activo' : 'inactivo'}">${user.activo ? 'Activo' : 'Inactivo'}</td>
                    <td>
                        <button class="editar">Editar</button>
                        <button class="eliminar">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            console.error('Error cargando usuarios:', data.message);
        }
    } catch (err) {
        console.error('Error de red mientras se obtenían usuarios', err);
    }

    // el botón "+ Nuevo usuario" redirige al formulario de registro
    const nuevoBtn = document.querySelector('.acciones .btn-primary');
    if (nuevoBtn) {
        nuevoBtn.addEventListener('click', () => {
            window.location.href = 'admin_registrar_usuario.html';
        });
    }

    // filtro de búsqueda en tiempo real
    const searchInput = document.querySelector('.acciones input[type=text]');
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            const term = searchInput.value.toLowerCase();
            document.querySelectorAll('.tabla tbody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }
});