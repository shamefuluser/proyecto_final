<?php
header('Content-Type: application/json');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Recoger datos del formulario
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
$userType = isset($_POST['userType']) ? $_POST['userType'] : 'visitante';

// Validar campos obligatorios
if (empty($nombre) || empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Nombre, email y contraseña son obligatorios']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
    exit;
}

if (!in_array($userType, ['visitante', 'nutriologo'])) {
    echo json_encode(['success' => false, 'message' => 'Tipo de usuario inválido']);
    exit;
}


$especialidad = isset($_POST['especialidad']) ? trim($_POST['especialidad']) : '';
$cedula_profesional = isset($_POST['cedula_profesional']) ? trim($_POST['cedula_profesional']) : '';
$bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';


if ($userType === 'nutriologo' && empty($cedula_profesional)) {
    echo json_encode(['success' => false, 'message' => 'La cédula profesional es obligatoria para nutriólogos']);
    exit;
}

try {
    $pdo = getConexion();
    
    // Verificar si el email ya está registrado
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este email ya está registrado']);
        exit;
    }
    
    
    $password_hash = password_hash($password, PASSWORD_BCRYPT);
    

    $pdo->beginTransaction();
    

    $stmt = $pdo->prepare("
        INSERT INTO usuarios (nombre, email, password, tipo_usuario, fecha_registro) 
        VALUES (?, ?, ?, ?, GETDATE())
    ");
    $stmt->execute([$nombre, $email, $password_hash, $userType]);
    
    
    $stmt = $pdo->query("SELECT @@IDENTITY AS id");
    $usuario_id = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
    
    
    if ($userType === 'nutriologo') {
        $stmt = $pdo->prepare("
            INSERT INTO perfiles_nutriologo (usuario_id, especialidad, cedula_profesional, telefono, bio) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$usuario_id, $especialidad, $cedula_profesional, $telefono, $bio]);
    }
    
    
    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'message' => '¡Registro exitoso! Redirigiendo al login...'
    ]);
    
} catch(Exception $e) {
    
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Error al registrar: ' . $e->getMessage()
    ]);
}
?>