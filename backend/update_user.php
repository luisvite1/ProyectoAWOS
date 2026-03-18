<?php
header('Content-Type: application/json');
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    
    $id = $data['id'];
    $username = $data['username'];
    $rol_id = $data['rol_id'];
    $activo = $data['activo'];
    $password = $data['password'];

    if (empty($username)) {
        echo json_encode(['success' => false, 'message' => 'El nombre de usuario es requerido']);
        exit;
    }

    // Construir consulta dinámica (si hay contraseña nueva o no)
    if (!empty($password)) {
        // Si escribieron contraseña, la actualizamos y encriptamos
        $pass_hash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE usuarios SET username = ?, rol_id = ?, activo = ?, password = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("siisi", $username, $rol_id, $activo, $pass_hash, $id);
    } else {
        // Si NO escribieron contraseña, mantenemos la anterior
        $sql = "UPDATE usuarios SET username = ?, rol_id = ?, activo = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("siii", $username, $rol_id, $activo, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar: ' . $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conexion->close();
?>