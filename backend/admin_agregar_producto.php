<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$d = json_decode(file_get_contents("php://input"), true);
$nombre      = trim($d['nombre']      ?? '');
$precio      = $d['precio']      ?? 0;
$stock       = $d['stock']       ?? 0;
$descripcion = trim($d['descripcion'] ?? '');
$categoria_id = $d['categoria_id'] ?? null;

if (!$nombre || !$categoria_id) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit;
}

$stmt = $conexion->prepare("INSERT INTO productos (nombre, precio, stock, descripcion, categoria_id, fecha_registro) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sddsi", $nombre, $precio, $stock, $descripcion, $categoria_id);
echo json_encode(['success' => $stmt->execute(), 'message' => $conexion->error]);