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

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';
$comentario_padre_id = isset($_POST['comentario_padre_id']) ? (int)$_POST['comentario_padre_id'] : null;
$usuario_id = $_SESSION['usuario_id'];

if ($post_id <= 0 || empty($contenido)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $pdo = getConexion();
    
    // Verificar que el post existe
    $stmt = $pdo->prepare("SELECT id FROM  PostsBlog WHERE id = ? AND activo = 1");
    $stmt->execute([$post_id]);
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Post no encontrado']);
        exit;
    }
    
    // Insertar comentario
    if ($comentario_padre_id) {
        
        $sql = "INSERT INTO comentarios_blog (post_id, autor_id, comentario_padre_id, contenido, fecha_comentario) 
                VALUES (?, ?, ?, ?, GETDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$post_id, $usuario_id, $comentario_padre_id, $contenido]);
    } else {
        
        $sql = "INSERT INTO comentarios_blog (post_id, autor_id, contenido, fecha_comentario) 
                VALUES (?, ?, ?, GETDATE())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$post_id, $usuario_id, $contenido]);
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Comentario publicado correctamente'
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al publicar comentario: ' . $e->getMessage()
    ]);
}
?>