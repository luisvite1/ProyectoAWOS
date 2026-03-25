<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// validar sesión
if (!isset($_SESSION['usuario'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No autorizado'
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$nombre     = $data['nombre']     ?? null;
$usuario_id = $data['usuario_id'] ?? $_SESSION['usuario']['id'];

if (!$nombre) {
    echo json_encode(['success' => false, 'message' => 'Nombre de mesa requerido']);
    exit;
}

$stmt = $conexion->prepare("INSERT INTO mesas (nombre, usuario_id) VALUES (?, ?)");
$stmt->bind_param("si", $nombre, $usuario_id);

if ($stmt->execute()) {
    $mesa_id = $stmt->insert_id;

    $stmtPedido = $conexion->prepare("INSERT INTO pedidos (mesa_id, usuario_id) VALUES (?, ?)");
    $stmtPedido->bind_param("ii", $mesa_id, $usuario_id);
    $stmtPedido->execute();

    echo json_encode(['success' => true, 'mesa_id' => $mesa_id]);
} else {
    echo json_encode(['success' => false, 'message' => $conexion->error]);
}