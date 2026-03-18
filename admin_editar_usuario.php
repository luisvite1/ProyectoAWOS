<?php
session_start();
include 'backend/config.php';

if (!isset($_GET['id'])) {
    header('Location: admin_usuarios.php');
    exit;
}

$id = $_GET['id'];

// Obtener datos del usuario
$stmt = $conexion->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) {
    echo "Usuario no encontrado";
    exit;
}

// Obtener roles para el select
$roles_res = $conexion->query("SELECT * FROM roles");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
    <link rel="stylesheet" href="frontend/css/global.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="container">

    <a href="admin_usuarios.php" class="back-btn">
        <i class="fas fa-arrow-left"></i> Volver
    </a>

    <div class="dashboard-card" style="max-width: 600px; margin: 2rem auto;">

        <div class="dashboard-card-header">
            <h1>Editar Usuario</h1>
        </div>

        <form id="editForm">

            <input type="hidden" name="id" value="<?php echo $usuario['id']; ?>">

            <div class="form-group">
                <label>Nombre de usuario</label>
                <input type="text" name="username"
                    value="<?php echo htmlspecialchars($usuario['username']); ?>" required>
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="rol_id" required>
                    <?php while($rol = $roles_res->fetch_assoc()): ?>
                        <option value="<?php echo $rol['id']; ?>"
                        <?php echo ($rol['id'] == $usuario['rol_id']) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($rol['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Estado</label>
                <select name="activo">
                    <option value="1" <?php echo ($usuario['activo'] == 1) ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo ($usuario['activo'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <div class="form-group">
                <label>Nueva Contraseña (opcional)</label>
                <input type="password" name="password"
                    placeholder="Dejar en blanco para no cambiar">
            </div>

            <button type="submit" class="btn btn-primary">
                Guardar Cambios
            </button>

        </form>

    </div>

</div>
</body>
</html>