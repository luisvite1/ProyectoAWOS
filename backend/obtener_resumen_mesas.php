<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$usuario_id = $_SESSION['usuario']['id'] ?? null;

if (!$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$sql = "
    SELECT 
        m.id,
        m.nombre,
        u.username AS mesero,
        COALESCE(SUM(pd.cantidad * pd.precio), 0) AS total
    FROM mesas m
    LEFT JOIN usuarios u ON m.usuario_id = u.id
    LEFT JOIN pedidos p ON p.mesa_id = m.id
    LEFT JOIN pedido_detalle pd ON pd.pedido_id = p.id
    WHERE m.usuario_id = ?
    GROUP BY m.id, m.nombre, u.username
    ORDER BY m.id DESC
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$resumen = [];

while ($row = $result->fetch_assoc()) {
    $resumen[] = $row;
}

echo json_encode([
    'success' => true,
    'resumen' => $resumen
]);