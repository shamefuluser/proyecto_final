<?php
include 'verificar_sesion.php';
verificarSesion(); 

require_once 'config.php';

$pdo = getConexion();
$usuario = getUsuarioActual();
$es_nutriologo = esNutriologo();

// Obtener categorías
$stmt = $pdo->query("SELECT * FROM CategoriasBlog ORDER BY nombre");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Filtros
$categoria_filtro = isset($_GET['categoria']) ? (int)$_GET['categoria'] : 0;
$busqueda = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';

// Construir query
$sql = "SELECT p.*, u.nombre as autor_nombre, u.tipo_usuario as autor_tipo, 
        c.nombre as categoria_nombre, c.color as categoria_color,
        (SELECT COUNT(*) FROM comentarios_blog WHERE post_id = p.id AND activo = 1) as num_comentarios,
        (SELECT COUNT(*) FROM likes_blog WHERE post_id = p.id) as num_likes
        FROM pOstsBlog p
        JOIN Usuarios u ON p.autor_id = u.id
        LEFT JOIN CategoriasBlog c ON p.categoria_id = c.id
        WHERE p.activo = 1";

$params = [];

if ($categoria_filtro > 0) {
    $sql .= " AND p.categoria_id = ?";
    $params[] = $categoria_filtro;
}

if (!empty($busqueda)) {
    $sql .= " AND (p.titulo LIKE ? OR p.contenido LIKE ?)";
    $params[] = "%$busqueda%";
    $params[] = "%$busqueda%";
}

$sql .= " ORDER BY p.fecha_publicacion DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>