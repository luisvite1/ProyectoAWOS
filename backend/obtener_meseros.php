<?php
include 'config.php';

$res = $conexion->query("SELECT id, username FROM usuarios WHERE rol_id = 2");

$meseros = [];

while($row = $res->fetch_assoc()){
    $meseros[] = $row;
}

echo json_encode(['meseros' => $meseros]);