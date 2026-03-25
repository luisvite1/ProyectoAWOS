<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$d  = json_decode(file_get_contents("php://input"), true);
$id = $d['id'] ?? null;

if (!$id) { echo json_encode(['success' => false, 'message' => 'ID requerido']); exit; }

$stmt = $conexion->prepare("DELETE FROM productos WHERE id_producto = ?");
$stmt->bind_param("i", $id);
echo json_encode(['success' => $stmt->execute(), 'message' => $conexion->error]);