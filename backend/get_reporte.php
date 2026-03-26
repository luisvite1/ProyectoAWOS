<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Total del día
$stmt = $conexion->prepare("
    SELECT COALESCE(SUM(pd.cantidad * pd.precio), 0) AS total_dia
    FROM pedidos p
    JOIN pedido_detalle pd ON pd.pedido_id = p.id
    WHERE DATE(p.fecha) = ?
");
$stmt->bind_param("s", $fecha);
$stmt->execute();
$total_dia = $stmt->get_result()->fetch_assoc()['total_dia'];

// Productos más vendidos
$stmt = $conexion->prepare("
    SELECT pr.nombre, SUM(pd.cantidad) AS cantidad_vendida, 
           SUM(pd.cantidad * pd.precio) AS subtotal
    FROM pedidos p
    JOIN pedido_detalle pd ON pd.pedido_id = p.id
    JOIN productos pr ON pr.id_producto = pd.producto_id
    WHERE DATE(p.fecha) = ?
    GROUP BY pr.id_producto, pr.nombre
    ORDER BY cantidad_vendida DESC
");
$stmt->bind_param("s", $fecha);
$stmt->execute();
$res = $stmt->get_result();
$productos = [];
while ($row = $res->fetch_assoc()) $productos[] = $row;

// Ventas por mesa
$stmt = $conexion->prepare("
    SELECT m.nombre AS mesa, SUM(pd.cantidad * pd.precio) AS total_mesa
    FROM pedidos p
    JOIN mesas m ON m.id = p.mesa_id
    JOIN pedido_detalle pd ON pd.pedido_id = p.id
    WHERE DATE(p.fecha) = ?
    GROUP BY m.id, m.nombre
    ORDER BY total_mesa DESC
");
$stmt->bind_param("s", $fecha);
$stmt->execute();
$res = $stmt->get_result();
$mesas = [];
while ($row = $res->fetch_assoc()) $mesas[] = $row;

// Ventas por mesero
$stmt = $conexion->prepare("
    SELECT u.username AS mesero, SUM(pd.cantidad * pd.precio) AS total_mesero
    FROM pedidos p
    JOIN usuarios u ON u.id = p.usuario_id
    JOIN pedido_detalle pd ON pd.pedido_id = p.id
    WHERE DATE(p.fecha) = ?
    GROUP BY u.id, u.username
    ORDER BY total_mesero DESC
");
$stmt->bind_param("s", $fecha);
$stmt->execute();
$res = $stmt->get_result();
$meseros = [];
while ($row = $res->fetch_assoc()) $meseros[] = $row;

echo json_encode([
    'success'    => true,
    'fecha'      => $fecha,
    'total_dia'  => $total_dia,
    'productos'  => $productos,
    'mesas'      => $mesas,
    'meseros'    => $meseros
]);