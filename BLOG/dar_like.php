<?php
session_start();
header('Content-Type: application/json');

require_once 'config.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : ''; // 'post' o 'comentario'
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$usuario_id = $_SESSION['usuario_id'];

if (empty($tipo) || $id <= 0 || !in_array($tipo, ['post', 'comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

try {
    $pdo = getConexion();
    
    if ($tipo === 'post') {
        
        $stmt = $pdo->prepare("SELECT id FROM likes_blog WHERE usuario_id = ? AND post_id = ?");
        $stmt->execute([$usuario_id, $id]);
        $existe = $stmt->fetch();
        
        if ($existe) {
           
            $stmt = $pdo->prepare("DELETE FROM likes_blog WHERE usuario_id = ? AND post_id = ?");
            $stmt->execute([$usuario_id,$id]);
            $accion = 'quitado';
        } else {
            
            $stmt = $pdo->prepare("INSERT INTO likes_blog (usuario_id, post_id, fecha_like) VALUES (?, ?, GETDATE())");
            $stmt->execute([$usuario_id, $id]);
            $accion = 'agregado';
        }
    } else {
       
        $stmt = $pdo->prepare("SELECT id FROM likes_blog WHERE usuario_id = ? AND comentario_id = ?");
        $stmt->execute([$usuario_id, $id]);
        $existe = $stmt->fetch();
        
        if ($existe) {
           
            $stmt = $pdo->prepare("DELETE FROM likes_blog WHERE usuario_id = ? AND comentario_id = ?");
            $stmt->execute([$usuario_id, $id]);
            $accion = 'quitado';
        } else {
            
            $stmt = $pdo->prepare("INSERT INTO likes_blog (usuario_id, comentario_id, fecha_like) VALUES (?, ?, GETDATE())");
            $stmt->execute([$usuario_id, $id]);
            $accion = 'agregado';
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Like ' . $accion,
        'accion' => $accion
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>