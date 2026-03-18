<?php
session_start();
include 'backend/config.php';

$sql = "SELECT u.id, u.username, u.activo, r.nombre as rol_nombre 
        FROM usuarios u 
        JOIN roles r ON u.rol_id = r.id 
        ORDER BY u.id ASC";

$result = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Administrar usuarios</title>

<link rel="stylesheet" href="frontend/css/global.css">
<link rel="stylesheet" href="frontend/css/admin_usuarios.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>

<header class="topbar">
    <a href="admin.php" class="back-btn">←</a>
    <h1>Administrar usuarios</h1>
</header>

<div class="container">

<div class="acciones">
    <a href="admin_registrar_usuario.html" class="btn btn-primary">
        <i class="fas fa-user-plus"></i> Nuevo usuario
    </a>

    <input type="text" id="buscador" placeholder="Buscar usuario...">
</div>

<table class="tabla">
<thead>
<tr>
<th>ID</th>
<th>Nombre</th>
<th>Rol</th>
<th>Estado</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): 
        $estadoClass = $row['activo'] ? 'status-active' : 'status-inactive';
        $estadoText = $row['activo'] ? 'Activo' : 'Inactivo';
    ?>
        <tr>
            <td>#<?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['username']) ?></td>
            <td><?= ucfirst($row['rol_nombre']) ?></td>
            <td>
                <span class="status-badge <?= $estadoClass ?>">
                    <?= $estadoText ?>
                </span>
            </td>

            <td class="actions">
                <a href="admin_editar_usuario.php?id=<?= $row['id'] ?>" 
                   class="btn-icon btn-edit">
                    <i class="fas fa-edit"></i>
                </a>

                <?php if ($_SESSION['usuario']['id'] != $row['id']): ?>
                <button class="btn-icon btn-delete"
                        onclick="eliminarUsuario(<?= $row['id'] ?>)">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" style="text-align:center; padding:2rem;">
            No hay usuarios registrados
        </td>
    </tr>
<?php endif; ?>

</tbody>
</table>

</div>

<!-- SOLO ESTE JS SE QUEDA -->
<script>
function eliminarUsuario(id) {
    if(confirm('¿Eliminar usuario?')) {
        fetch('backend/delete_user.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id })
        })
        .then(res => res.json())
        .then(data => {
            if(data.success){
                location.reload();
            } else {
                alert(data.message);
            }
        });
    }
}

// buscador (esto sí es frontend)
document.getElementById('buscador').addEventListener('input', function() {
    let filtro = this.value.toLowerCase();
    document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(filtro) ? '' : 'none';
    });
});
</script>

</body>
</html>