<?php
header('Content-Type: application/json');
include 'config.php';
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'] ?? null;

if (!$id) {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
    exit;
}

if ($id == $_SESSION['usuario']['id']) {
    echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
    exit;
}

// Obtener username ANTES de eliminar
$stmt2 = $conexion->prepare("SELECT username FROM usuarios WHERE id = ?");
$stmt2->bind_param("i", $id);
$stmt2->execute();
$row = $stmt2->get_result()->fetch_assoc();
$username_eliminar = $row['username'] ?? '';

// Eliminar de MySQL
$stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    // Sincronizar con MongoDB por username
    $ch = curl_init('http://localhost:3000/usuarios/username/' . urlencode($username_eliminar));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $conexion->error]);
}

$conexion->close();
?>