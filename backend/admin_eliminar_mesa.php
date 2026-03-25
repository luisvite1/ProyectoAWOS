<?php
session_start();
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

// 1. eliminar detalle pedido
$stmt = $conexion->prepare("
    DELETE pd FROM pedido_detalle pd
    JOIN pedidos p ON pd.pedido_id = p.id
    WHERE p.mesa_id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();

// 2. eliminar pedidos
$stmt2 = $conexion->prepare("DELETE FROM pedidos WHERE mesa_id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();

// 3. eliminar mesa
$stmt3 = $conexion->prepare("DELETE FROM mesas WHERE id = ?");
$stmt3->bind_param("i", $id);
$stmt3->execute();

echo json_encode(['success' => true]);