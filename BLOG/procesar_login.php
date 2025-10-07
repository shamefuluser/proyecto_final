<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$email = isset($_POST['email']) ? limpiarDato($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$userType = isset($_POST['userType']) ? $_POST['userType'] : 'visitante';
$remember = isset($_POST['remember']);

if (empty($email) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Email y contraseña son requeridos']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Email no válido']);
    exit;
}

try {
    $pdo = getConexion();
    
    $sql = "SELECT id, nombre, email, password, tipo_usuario, activo 
            FROM usuarios 
            WHERE email = ? AND tipo_usuario = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email, $userType]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode([
            'success' => false, 
            'message' => 'Credenciales incorrectas o tipo de usuario incorrecto'
        ]);
        exit;
    }
    
    if (!$usuario['activo']) {
        echo json_encode([
            'success' => false, 
            'message' => 'Tu cuenta ha sido desactivada'
        ]);
        exit;
    }
    
    if (!password_verify($password, $usuario['password'])) {
        echo json_encode([
            'success' => false, 
            'message' => 'Credenciales incorrectas'
        ]);
        exit;
    }
    
    
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_email'] = $usuario['email'];
    $_SESSION['usuario_tipo'] = $usuario['tipo_usuario'];
    $_SESSION['login_time'] = time();
    
    
    $stmt = $pdo->prepare("UPDATE usuarios SET ultimo_acceso = GETDATE() WHERE id = ?");
    $stmt->execute([$usuario['id']]);
    
    
    if ($remember) {
        $token = bin2hex(random_bytes(32));
        $expiracion = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $stmt = $pdo->prepare("INSERT INTO sesiones (usuario_id, token, ip_address, user_agent, fecha_expiracion) 
                              VALUES (?, ?, ?, ?, ?)");
        
        $stmt->execute([
            $usuario['id'],
            $token,
            $_SERVER['REMOTE_ADDR'],
            $_SERVER['HTTP_USER_AGENT'] ?? '',
            $expiracion
        ]);
        
        setcookie('remember_token', $token, strtotime('+30 days'), '/', '', false, true);
    }
    
    
    $redirect = ($usuario['tipo_usuario'] === 'nutriologo') ? 'panel_nutriologo.php' : 'pagina_inicio.php';
    
    echo json_encode([
        'success' => true,
        'message' => 'Inicio de sesión exitoso',
        'redirect' => $redirect,
        'usuario' => [
            'nombre' => $usuario['nombre'],
            'tipo' => $usuario['tipo_usuario']
        ]
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error del sistema: ' . $e->getMessage()
    ]);
}
?>