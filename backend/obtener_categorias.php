<?php
include 'config.php';
header('Content-Type: application/json');

$result = $conexion->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC");
$cats = [];
while ($row = $result->fetch_assoc()) $cats[] = $row;
echo json_encode(['success' => true, 'categorias' => $cats]);