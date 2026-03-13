<?php
/**
 * Maneja el proceso de login de usuarios.
 * Verifica credenciales y establece la sesión si es válido.
 */

session_start();
header('Content-Type: application/json');

include 'config.php';

$datos = json_decode(file_get_contents("php://input"), true);

$username = $datos['username'] ?? '';
$password = $datos['password'] ?? '';

if(empty($username) || empty($password)){
    echo json_encode([
        "success" => false,
        "message" => "Campos vacíos"
    ]);
    exit;
}

$sql = "SELECT * FROM usuarios WHERE username = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s",$username);
$stmt->execute();
$resultado = $stmt->get_result();

if($resultado->num_rows === 0){
    echo json_encode([
        "success"=>false,
        "message"=>"Usuario o contraseña incorrectos"
    ]);
    exit;
}

$usuario = $resultado->fetch_assoc();

$password_valida = password_verify($password,$usuario['password']) || $password === $usuario['password'];

if(!$password_valida){
    echo json_encode([
        "success"=>false,
        "message"=>"Usuario o contraseña incorrectos"
    ]);
    exit;
}

$_SESSION['usuario'] = [
    "id"=>$usuario['id'],
    "username"=>$usuario['username'],
    "rol_id"=>$usuario['rol_id']
];

$_SESSION['login_time'] = date('Y-m-d H:i:s');

echo json_encode([
    "success"=>true,
    "usuario"=>$_SESSION['usuario']
]);
?>