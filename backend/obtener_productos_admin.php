<?php
include 'config.php';
header('Content-Type: application/json');

$sql = "
    SELECT p.id_producto, p.nombre, p.precio, p.categoria_id, p.descripcion, p.stock,
           c.nombre AS categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.categoria_id = c.id
    ORDER BY p.nombre ASC
";
$result = $conexion->query($sql);
$productos = [];
while ($row = $result->fetch_assoc()) $productos[] = $row;
echo json_encode(['success' => true, 'productos' => $productos]);