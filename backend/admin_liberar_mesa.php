<?php
session_start();
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

// liberar mesa
$stmt = $conexion->prepare("UPDATE mesas SET estado = 'libre' WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// cerrar pedido activo
$stmt2 = $conexion->prepare("
    UPDATE pedidos 
    SET estado = 'cerrado' 
    WHERE mesa_id = ? AND estado = 'activo'
");
$stmt2->bind_param("i", $id);
$stmt2->execute();

echo json_encode(['success' => true]);