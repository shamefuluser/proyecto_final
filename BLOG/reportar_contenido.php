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
$motivo = isset($_POST['motivo']) ? trim($_POST['motivo']) : '';
$usuario_id = $_SESSION['usuario_id'];

if (empty($tipo) || $id <= 0 || empty($motivo) || !in_array($tipo, ['post', 'comentario'])) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $pdo = getConexion();
    
    // Verificar que no haya reportado el mismo contenido antes
    $stmt = $pdo->prepare("SELECT id FROM reportes_blog WHERE reportador_id = ? AND tipo_contenido = ? AND contenido_id = ?");
    $stmt->execute([$usuario_id, $tipo, $id]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Ya has reportado este contenido anteriormente']);
        exit;
    }
    
    // hacer el reporte
    $sql = "INSERT INTO reportes_blog (reportador_id, tipo_contenido, contenido_id, motivo, fecha_reporte) 
            VALUES (?, ?, ?, ?, GETDATE())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $tipo, $id, $motivo]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Reporte enviado correctamente. Será revisado por el equipo de desarrollo.'
    ]);
    
} catch(Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al enviar reporte: ' . $e->getMessage()
    ]);
}
?>