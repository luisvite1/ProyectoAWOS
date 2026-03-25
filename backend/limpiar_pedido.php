<?php
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$mesa_id = $data['mesa_id'];

$stmt = $conexion->prepare("SELECT id FROM pedidos WHERE mesa_id = ?");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $pedido_id = $res->fetch_assoc()['id'];

    $stmt = $conexion->prepare("DELETE FROM pedido_detalle WHERE pedido_id = ?");
    $stmt->bind_param("i", $pedido_id);
    $stmt->execute();
}

echo json_encode(['success' => true]);