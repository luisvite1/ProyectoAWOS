<?php
include 'config.php';

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$pedido_id = $data['pedido_id'];

$sql = "
SELECT 
    pd.cantidad,
    pd.precio,
    pr.nombre
FROM pedido_detalle pd
JOIN productos pr ON pd.producto_id = pr.id_producto
WHERE pd.pedido_id = ?
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $pedido_id);
$stmt->execute();

$result = $stmt->get_result();

$items = [];

while ($row = $result->fetch_assoc()) {
    $items[] = $row;
}

echo json_encode([
    'success' => true,
    'items' => $items
]);