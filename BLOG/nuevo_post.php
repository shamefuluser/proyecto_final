<?php
include 'verificar_sesion.php';
verificarSesion('nutriologo'); // Asegurarse de que el usuario es un nutriologo

require_once 'config.php';
$pdo = getConexion();

$usuario = getUsuarioActual();

// Obtener categorías
$stmt = $pdo->query("SELECT * FROM CategoriasBlog ORDER BY nombre");
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $contenido = isset($_POST['contenido']) ? trim($_POST['contenido']) : '';
    $categoria_id = isset($_POST['categoria_id']) ? (int)$_POST['categoria_id'] : null;
    
    if (empty($titulo) || empty($contenido)) {
        $mensaje = 'Título y contenido son obligatorios';
        $tipo_mensaje = 'error';
    } else {
        try {
            $sql = "INSERT INTO PostsBlog (autor_id, categoria_id, titulo, contenido, fecha_publicacion) 
                    VALUES (?, ?, ?, ?, GETDATE())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$usuario['id'], $categoria_id, $titulo, $contenido]);
            
            $mensaje = '¡Post publicado exitosamente!';
            $tipo_mensaje = 'success';
            
            // Limpiar formulario
            $_POST = array();
        } catch(Exception $e) {
            $mensaje = 'Error al publicar: ' . $e->getMessage();
            $tipo_mensaje = 'error';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Post - Bite & Balance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="stylenuevo_post.css">
    <link rel="shortcut icon" href="IMAGENESb/logo22.png" type="image/x-icon">
    
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-leaf"></i> Bite & Balance</h1>
        <div style="display: flex; gap: 15px;">
            <a href="pagina_inicio.php"><i class="fas fa-blog"></i> Blog</a>
            <a href="panel_nutriologo.php"><i class="fas fa-chart-line"></i> Panel</a>
            <a href="cerrarsesion.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>

    <div class="container">
        <a href="pagina_inicio.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver al Blog
        </a>

        <div class="form-container">
            <div class="form-header">
                <h2><i class="fas fa-pen"></i> Crear Nuevo Post</h2>
                <p>Comparte tus conocimientos y consejos con la comunidad</p>
            </div>

            <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>">
                <i class="fas fa-<?php echo $tipo_mensaje === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="titulo">
                        <i class="fas fa-heading"></i> Título del Post *
                    </label>
                    <input 
                        type="text" 
                        id="titulo" 
                        name="titulo" 
                        placeholder="Ej: 5 Consejos para una Alimentación Saludable"
                        required
                        maxlength="255"
                        value="<?php echo isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : ''; ?>"
                    >
                    <div class="char-count">
                        <span id="titulo-count">0</span>/255 caracteres
                    </div>
                </div>

                <div class="form-group">
                    <label for="categoria_id">
                        <i class="fas fa-folder"></i> Categoría
                    </label>
                    <select id="categoria_id" name="categoria_id">
                        <option value="">Sin categoría</option>
                        <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" 
                            <?php echo (isset($_POST['categoria_id']) && $_POST['categoria_id'] == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="contenido">
                        <i class="fas fa-file-alt"></i> Contenido *
                    </label>
                    <textarea 
                        id="contenido" 
                        name="contenido" 
                        placeholder="Escribe aquí el contenido de tu post..."
                        required
                    ><?php echo isset($_POST['contenido']) ? htmlspecialchars($_POST['contenido']) : ''; ?></textarea>
                    <div class="char-count">
                        <span id="contenido-count">0</span> caracteres
                    </div>
                </div>

                <div class="form-actions">
                    <a href="pagina_inicio.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publicar Post
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Contador de caracteres para título
        const tituloInput = document.getElementById('titulo');
        const tituloCount = document.getElementById('titulo-count');
        
        tituloInput.addEventListener('input', function() {
            tituloCount.textContent = this.value.length;
        });
        
        // Contador de caracteres para contenido
        const contenidoTextarea = document.getElementById('contenido');
        const contenidoCount = document.getElementById('contenido-count');
        
        contenidoTextarea.addEventListener('input', function() {
            contenidoCount.textContent = this.value.length;
        });
        
        // Inicializar contadores si hay contenido previo
        tituloCount.textContent = tituloInput.value.length;
        contenidoCount.textContent = contenidoTextarea.value.length;
    </script>
</body>
</html>