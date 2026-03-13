<?php
/**
 * Obtiene datos de resumen para el panel de administrador.
 * Incluye conteo de usuarios activos y mesas registradas.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'config.php';

session_start();

// Usuarios activos
$sql_activos = "SELECT COUNT(*) as total FROM usuarios WHERE activo = 1";
$result_activos = $conexion->query($sql_activos);
$activos = $result_activos->fetch_assoc()['total'];

// Si no hay activos, contar todos para debug
if ($activos == 0) {
    $sql_activos = "SELECT COUNT(*) as total FROM usuarios";
    $result_activos = $conexion->query($sql_activos);
    $activos = $result_activos->fetch_assoc()['total'];
}

// Mesas registradas (opcional, pero incluido)
$mesas = 0;
$sql_mesas = "SELECT COUNT(*) as total FROM mesas";
$result_mesas = $conexion->query($sql_mesas);
if ($result_mesas) {
    $mesas = $result_mesas->fetch_assoc()['total'];
}

echo json_encode([
    'success' => true,
    'usuarios_activos' => $activos,
    'mesas_registradas' => $mesas
]);

$conexion->close();
?>