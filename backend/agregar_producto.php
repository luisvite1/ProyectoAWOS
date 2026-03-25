<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

$data = json_decode(file_get_contents("php://input"), true);

$mesa_id = $data['mesa_id'] ?? null;
$producto_id = $data['producto_id'] ?? null;

if (!$mesa_id || !$producto_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// 🔥 BUSCAR PEDIDO DE ESA MESA
$stmt = $conexion->prepare("SELECT id FROM pedidos WHERE mesa_id = ?");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $pedido_id = $res->fetch_assoc()['id'];
} else {
    // 🔥 CREAR PEDIDO CON USUARIO
    $stmt = $conexion->prepare("INSERT INTO pedidos (mesa_id, usuario_id, total, fecha) VALUES (?, ?, 0, NOW())");
    $stmt->bind_param("ii", $mesa_id, $usuario_id);
    $stmt->execute();

    $pedido_id = $stmt->insert_id;
}

// 🔥 OBTENER PRECIO
$stmt = $conexion->prepare("SELECT precio FROM productos WHERE id_producto = ?");
$stmt->bind_param("i", $producto_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'Producto no existe']);
    exit;
}

$precio = $res->fetch_assoc()['precio'];

// 🔥 VER SI YA EXISTE
$stmt = $conexion->prepare("SELECT id, cantidad FROM pedido_detalle WHERE pedido_id = ? AND producto_id = ?");
$stmt->bind_param("ii", $pedido_id, $producto_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $item = $res->fetch_assoc();
    $nueva = $item['cantidad'] + 1;

    $stmt = $conexion->prepare("UPDATE pedido_detalle SET cantidad = ? WHERE id = ?");
    $stmt->bind_param("ii", $nueva, $item['id']);
    $stmt->execute();
} else {
    $stmt = $conexion->prepare("INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, 1, ?)");
    $stmt->bind_param("iid", $pedido_id, $producto_id, $precio);
    $stmt->execute();
}

echo json_encode(['success' => true]);

$stmt = $conexion->prepare("
    UPDATE pedidos 
    SET total = (
        SELECT SUM(cantidad * precio)
        FROM pedido_detalle
        WHERE pedido_id = ?
    )
    WHERE id = ?
");
$stmt->bind_param("ii", $pedido_id, $pedido_id);
$stmt->execute();