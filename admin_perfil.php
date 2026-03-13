<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil</title>

<link rel="stylesheet" href="frontend/css/global.css">
<link rel="stylesheet" href="frontend/css/admin_perfil.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&family=Inter:wght@300;400&display=swap" rel="stylesheet">
</head>

<body>

<header class="topbar">
    <a href="admin.php" class="back-btn">←</a>
    <h1>Perfil</h1>
</header>

<div class="perfil-card">
<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: index.html');
    exit;
}

include 'backend/config.php';

$user = $_SESSION['usuario'];
$id = $user['id'];
$username = $user['username'];
$rol_id = $user['rol_id'];

// Get role name
$sql = "SELECT nombre FROM roles WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $rol_id);
$stmt->execute();
$result = $stmt->get_result();
$rol = $result->fetch_assoc()['nombre'];

// Get user details
$sql_user = "SELECT * FROM usuarios WHERE id = ?";
$stmt_user = $conexion->prepare($sql_user);
$stmt_user->bind_param("i", $id);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();

$email = $user_data['email'] ?? 'No disponible'; // Assuming email column exists
$fecha_registro = $user_data['fecha_registro'] ?? 'No disponible';

echo "<h2>$rol</h2>";
echo "<p><b>Nombre:</b> $username</p>";
echo "<p><b>Correo:</b> $email</p>";
echo "<p><b>Rol:</b> $rol</p>";
echo "<p><b>Fecha de registro:</b> $fecha_registro</p>";
?>
<button>Editar perfil</button>
</div>

</body>
</html>