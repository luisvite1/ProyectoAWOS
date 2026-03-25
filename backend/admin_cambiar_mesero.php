<?php
session_start();
include 'config.php';

$data = json_decode(file_get_contents("php://input"), true);

$mesa_id = $data['mesa_id'];
$usuario_id = $data['usuario_id'];

// actualizar mesa
$stmt = $conexion->prepare("UPDATE mesas SET usuario_id = ? WHERE id = ?");
$stmt->bind_param("ii", $usuario_id, $mesa_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}