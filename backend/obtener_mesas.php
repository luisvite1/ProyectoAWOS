<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false]);
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

$sql = "SELECT id, nombre FROM mesas WHERE usuario_id = ? ORDER BY nombre ASC";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$result = $stmt->get_result();

$mesas = [];

while ($row = $result->fetch_assoc()) {
    $mesas[] = $row;
}

echo json_encode([
    'success' => true,
    'mesas' => $mesas
]);