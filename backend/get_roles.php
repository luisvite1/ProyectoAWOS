<?php
/**
 * Obtiene la lista de roles disponibles en el sistema.
 * Devuelve un JSON con los roles para usar en formularios de registro.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'config.php';

$sql = "SELECT id, nombre FROM roles";
$result = $conexion->query($sql);

if (!$result) {
    echo json_encode(['success' => false, 'message' => $conexion->error]);
    exit;
}

$roles = [];
while ($row = $result->fetch_assoc()) {
    $roles[] = $row;
}

echo json_encode(['success' => true, 'roles' => $roles]);

$conexion->close();
?>