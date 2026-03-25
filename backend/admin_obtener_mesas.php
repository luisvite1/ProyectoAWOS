<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false]);
    exit;
}

$sql = "
SELECT 
    m.id,
    m.nombre,
    u.username AS mesero,
    IFNULL(SUM(pd.cantidad * pd.precio), 0) AS total,
    p.id AS pedido_id
FROM mesas m
LEFT JOIN usuarios u ON m.usuario_id = u.id
LEFT JOIN pedidos p ON m.id = p.mesa_id
LEFT JOIN pedido_detalle pd ON pd.pedido_id = p.id
GROUP BY m.id, m.nombre, u.username, p.id
ORDER BY m.id DESC
";

$result = $conexion->query($sql);

$mesas = [];

while ($row = $result->fetch_assoc()) {
    $mesas[] = $row;
}

echo json_encode([
    'success' => true,
    'mesas' => $mesas
]);