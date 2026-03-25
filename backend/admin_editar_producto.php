<?php
session_start();
include 'config.php';
header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']); exit;
}

$d = json_decode(file_get_contents("php://input"), true);
$id          = $d['id']          ?? null;
$nombre      = trim($d['nombre'] ?? '');
$precio      = $d['precio']      ?? 0;
$stock       = $d['stock']       ?? 0;
$descripcion = trim($d['descripcion'] ?? '');
$categoria_id = $d['categoria_id'] ?? null;

if (!$id || !$nombre) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']); exit;
}

$stmt = $conexion->prepare("UPDATE productos SET nombre=?, precio=?, stock=?, descripcion=?, categoria_id=? WHERE id_producto=?");
$stmt->bind_param("sddsii", $nombre, $precio, $stock, $descripcion, $categoria_id, $id);
echo json_encode(['success' => $stmt->execute(), 'message' => $conexion->error]);