<?php
/**
 * Obtiene la lista de usuarios registrados en el sistema.
 * Incluye información como nombre, rol y estado de actividad.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'config.php';

$sql = "SELECT u.id, u.username, u.activo, r.nombre AS rol_nombre FROM usuarios u LEFT JOIN roles r ON u.rol_id = r.id";
$result = $conexion->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => $conexion->error]);
    exit;
}

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['success' => true, 'users' => $users]);

$conexion->close();
?>