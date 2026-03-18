<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

// validar admin
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false]);
    exit;
}

$sql = "
SELECT 
    m.id,
    m.nombre,
    m.estado,
    u.username AS mesero,
    IFNULL(p.total, 0) AS total,
    p.id AS pedido_id
FROM mesas m
LEFT JOIN usuarios u ON m.usuario_id = u.id
LEFT JOIN pedidos p ON m.id = p.mesa_id AND p.estado = 'abierto'
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