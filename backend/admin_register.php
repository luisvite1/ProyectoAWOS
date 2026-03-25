<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// Solo admins pueden usar este endpoint
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$datos = json_decode(file_get_contents("php://input"), true);

$username = trim($datos['username'] ?? '');
$password = $datos['password'] ?? '';
$rol      = $datos['rol'] ?? '';

if (empty($username) || empty($password) || empty($rol)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
    exit;
}

// Obtener rol_id
$stmt = $conexion->prepare("SELECT id FROM roles WHERE nombre = ?");
$stmt->bind_param("s", $rol);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Rol no válido']);
    exit;
}
$rol_id = $res->fetch_assoc()['id'];

// Verificar si ya existe
$stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
    exit;
}

// Insertar
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conexion->prepare("INSERT INTO usuarios (username, password, rol_id, activo) VALUES (?, ?, ?, 1)");
$stmt->bind_param("ssi", $username, $hash, $rol_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => $conexion->error]);
}