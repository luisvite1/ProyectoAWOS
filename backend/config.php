<?php
/**
 * Configuración de conexión a la base de datos MySQL.
 * Establece la conexión para el sistema de restaurante.
 */

 // Configuración de conexión a la base de datos

$host = 'sql206.infinityfree.com';
$usuario = 'if0_41648362';
$contraseña = 'Luis122vl';
$base_datos = 'if0_41648362_los_litros';

// Crear conexión
$conexion = new mysqli($host, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
}

// Establecer el conjunto de caracteres
$conexion->set_charset("utf8");
?>
