<?php
/**
 * Configuración de conexión a la base de datos MySQL.
 * Establece la conexión para el sistema de restaurante.
 */

 // Configuración de conexión a la base de datos

$host = 'localhost';
$usuario = 'root'; // Usuario por defecto en XAMPP
$contraseña = ''; // Sin contraseña por defecto en XAMPP
$base_datos = 'los_litros'; // Nombre de tu base de datos

// Crear conexión
$conexion = new mysqli($host, $usuario, $contraseña, $base_datos);

// Verificar conexión
if ($conexion->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']));
}

// Establecer el conjunto de caracteres
$conexion->set_charset("utf8");
?>
