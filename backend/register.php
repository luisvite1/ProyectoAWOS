<?php
/**
 * Maneja el registro de nuevos usuarios en el sistema.
 * Valida datos y crea cuentas para roles específicos.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $datos = json_decode(file_get_contents("php://input"), true);
    
    $username = $conexion->real_escape_string($datos['username']);
    $password = $datos['password'];
    $rol = $conexion->real_escape_string($datos['rol']);
    
    // Validar que los campos no estén vacíos
    if (empty($username) || empty($password) || empty($rol)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }
    
    // Obtener el rol_id del nombre del rol
    $rol_query = $conexion->query("SELECT id FROM roles WHERE nombre = '$rol'");
    if ($rol_query->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Rol no válido']);
        exit;
    }
    $rol_data = $rol_query->fetch_assoc();
    $rol_id = $rol_data['id'];
    
    // Verificar si el usuario ya existe
    $verificar = $conexion->query("SELECT id FROM usuarios WHERE username = '$username'");
    
    if ($verificar->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
        exit;
    }
    
    // Encriptar la contraseña
    $password_encriptada = password_hash($password, PASSWORD_BCRYPT);
    
    // Insertar el nuevo usuario
    $sql = "INSERT INTO usuarios (username, password, rol_id, activo) VALUES ('$username', '$password_encriptada', '$rol_id', 1)";
    
    if ($conexion->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $conexion->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conexion->close();
?>
