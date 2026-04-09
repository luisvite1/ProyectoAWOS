<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

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

// Insertar en MySQL primero
$hash = password_hash($password, PASSWORD_BCRYPT);
$stmt = $conexion->prepare("INSERT INTO usuarios (username, password, rol_id, activo) VALUES (?, ?, ?, 1)");
$stmt->bind_param("ssi", $username, $hash, $rol_id);

if ($stmt->execute()) {
    $nuevo_id = $conexion->insert_id;

    // Sincronizar con MongoDB después de tener el mysql_id
    $mongo_data = json_encode([
        'username' => $username,
        'password' => $hash,
        'rol'      => $rol,
        'activo'   => true,
        'mysql_id' => $nuevo_id
    ]);

    $ch = curl_init('http://localhost:3000/usuarios');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $mongo_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
} else {
    echo json_encode(['success' => false, 'message' => $conexion->error]);
}