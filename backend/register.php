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
    
    $username = $datos['username'];
    $password = $datos['password'];
    $rol = $datos['rol'];
    
    // Validar que los campos no estén vacíos
    if (empty($username) || empty($password) || empty($rol)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }
    
    // Seguridad: Impedir que se registren administradores desde el formulario público
    if (strtolower($rol) === 'administrador') {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para registrar una cuenta de administrador']);
        exit;
    }
    
    // Obtener el rol_id del nombre del rol
    $stmt = $conexion->prepare("SELECT id FROM roles WHERE nombre = ?");
    $stmt->bind_param("s", $rol);
    $stmt->execute();
    $rol_query = $stmt->get_result();
    if ($rol_query->num_rows == 0) {
        echo json_encode(['success' => false, 'message' => 'Rol no válido']);
        exit;
    }
    $rol_data = $rol_query->fetch_assoc();
    $rol_id = $rol_data['id'];
    
    // Verificar si el usuario ya existe
    $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $verificar = $stmt->get_result();
    
    if ($verificar->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'El usuario ya existe']);
        exit;
    }
    
    // Encriptar la contraseña
    $password_encriptada = password_hash($password, PASSWORD_BCRYPT);
    
    // Insertar el nuevo usuario
    $stmt = $conexion->prepare("INSERT INTO usuarios (username, password, rol_id, activo) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("ssi", $username, $password_encriptada, $rol_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conexion->close();
?>
