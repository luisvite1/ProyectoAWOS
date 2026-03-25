<?php
include 'config.php';

$mesa_id = $_GET['mesa_id'] ?? null;

if (!$mesa_id) {
    echo json_encode(['success' => false]);
    exit;
}

$stmt = $conexion->prepare("SELECT id FROM pedidos WHERE mesa_id = ?");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows == 0) {
    echo json_encode(['success' => true, 'items' => [], 'total' => 0]);
    exit;
}

$pedido_id = $res->fetch_assoc()['id'];

$stmt = $conexion->prepare("
    SELECT p.nombre, d.cantidad, (d.cantidad * d.precio) AS subtotal
    FROM pedido_detalle d
    JOIN productos p ON d.producto_id = p.id_producto
    WHERE d.pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$res = $stmt->get_result();

$items = [];
$total = 0;

while ($row = $res->fetch_assoc()) {
    $items[] = $row;
    $total += $row['subtotal'];
}

echo json_encode([
    'success' => true,
    'items' => $items,
    'total' => $total
]);