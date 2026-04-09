<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$data = json_decode(file_get_contents("php://input"), true);

$mesa_id  = $data['mesa_id']  ?? null;
$productos = $data['productos'] ?? [];

if (!$mesa_id || empty($productos)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Buscar o crear pedido de la mesa
$stmt = $conexion->prepare("SELECT id FROM pedidos WHERE mesa_id = ?");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows > 0) {
    $pedido_id = $res->fetch_assoc()['id'];
} else {
    $stmt = $conexion->prepare("INSERT INTO pedidos (mesa_id, usuario_id, total, fecha) VALUES (?, ?, 0, NOW())");
    $stmt->bind_param("ii", $mesa_id, $usuario_id);
    $stmt->execute();
    $pedido_id = $stmt->insert_id;
}

// Insertar o acumular cada producto
foreach ($productos as $item) {
    $producto_id = $item['producto_id'] ?? null;
    $cantidad    = $item['cantidad']    ?? 1;

    if (!$producto_id) continue;

    $stmt = $conexion->prepare("SELECT precio FROM productos WHERE id_producto = ?");
    $stmt->bind_param("i", $producto_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows == 0) continue;
    $precio = $res->fetch_assoc()['precio'];

    $stmt = $conexion->prepare("SELECT id, cantidad FROM pedido_detalle WHERE pedido_id = ? AND producto_id = ?");
    $stmt->bind_param("ii", $pedido_id, $producto_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $nueva = $row['cantidad'] + $cantidad;
        $stmt = $conexion->prepare("UPDATE pedido_detalle SET cantidad = ? WHERE id = ?");
        $stmt->bind_param("ii", $nueva, $row['id']);
        $stmt->execute();
    } else {
        $stmt = $conexion->prepare("INSERT INTO pedido_detalle (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $pedido_id, $producto_id, $cantidad, $precio);
        $stmt->execute();
    }
}

// Descontar stock
foreach ($productos as $item) {
    $producto_id = $item['producto_id'] ?? null;
    $cantidad    = $item['cantidad']    ?? 1;
    if (!$producto_id) continue;

    $stmt = $conexion->prepare("UPDATE productos SET stock = GREATEST(stock - ?, 0) WHERE id_producto = ?");
    $stmt->bind_param("ii", $cantidad, $producto_id);
    $stmt->execute();
}

// Actualizar total del pedido en MySQL
$stmt = $conexion->prepare("
    UPDATE pedidos 
    SET total = (SELECT SUM(cantidad * precio) FROM pedido_detalle WHERE pedido_id = ?)
    WHERE id = ?
");
$stmt->bind_param("ii", $pedido_id, $pedido_id);
$stmt->execute();

// Obtener nombre de mesa y mesero
$mesa_nombre = '';
$stmt = $conexion->prepare("SELECT nombre FROM mesas WHERE id = ?");
$stmt->bind_param("i", $mesa_id);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) $mesa_nombre = $row['nombre'];

$mesero = $_SESSION['usuario']['username'] ?? 'desconocido';

// Calcular total desde BD para MongoDB
$total_mongo = 0;
foreach ($productos as $item) {
    $pid = $item['producto_id'];
    $qty = $item['cantidad'];
    $stmt_precio = $conexion->prepare("SELECT precio FROM productos WHERE id_producto = ?");
    $stmt_precio->bind_param("i", $pid);
    $stmt_precio->execute();
    $precio_res = $stmt_precio->get_result()->fetch_assoc();
    $total_mongo += $qty * ($precio_res['precio'] ?? 0);
}

// Registrar venta en MongoDB
$venta_data = json_encode([
    'mesa_id'     => $mesa_id,
    'mesa_nombre' => $mesa_nombre,
    'mesero'      => $mesero,
    'productos'   => $productos,
    'total'       => $total_mongo
]);

$ch = curl_init('http://localhost:3000/ventas');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $venta_data);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

echo json_encode(['success' => true, 'message' => 'Pedido guardado correctamente']);