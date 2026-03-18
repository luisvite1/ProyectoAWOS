<?php
header('Content-Type: application/json');
include 'config.php';
session_start();

// Verificar si es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) { // Asumiendo que 1 es admin
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

// Prevenir auto-eliminación
if ($id == $_SESSION['usuario']['id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $conexion->error]);
}
?>