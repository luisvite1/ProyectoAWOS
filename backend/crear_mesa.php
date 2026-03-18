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

$nombre = $data['nombre'] ?? null;
$estado = $data['estado'] ?? 'ocupada';
$usuario_id = $_SESSION['usuario']['id'];

if (!$nombre) {
    echo json_encode([
        'success' => false,
        'message' => 'Nombre de mesa requerido'
    ]);
    exit;
}

// ✅ INSERT CORRECTO
$stmt = $conexion->prepare("
    INSERT INTO mesas (nombre, usuario_id, estado) 
    VALUES (?, ?, ?)
");

$stmt->bind_param("sis", $nombre, $usuario_id, $estado);

if ($stmt->execute()) {

    $mesa_id = $stmt->insert_id;

    // ✅ crear pedido automáticamente
    $stmtPedido = $conexion->prepare("
        INSERT INTO pedidos (mesa_id, usuario_id) 
        VALUES (?, ?)
    ");
    $stmtPedido->bind_param("ii", $mesa_id, $usuario_id);
    $stmtPedido->execute();

    echo json_encode([
        'success' => true,
        'mesa_id' => $mesa_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear mesa'
    ]);
}