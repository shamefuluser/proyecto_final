<?php
require_once 'verificar_sesion.php';
verificarSesion(); 

require_once 'config.php';
$pdo = getConexion();

$usuario = getUsuarioActual();
$nutriologo_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($nutriologo_id <= 0) {
    header('Location: nutriologos.php');
    exit;
}


$stmt = $pdo->prepare("
    SELECT u.*, p.especialidad, p.cedula_profesional, p.telefono, p.bio, p.foto_perfil
    FROM usuarios u
    LEFT JOIN PerfilesNutriologo p ON u.id = p.usuario_id
    WHERE u.id = ? AND u.tipo_usuario = 'nutriologo' AND u.activo = 1
");
$stmt->execute([$nutriologo_id]);
$nutriologo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$nutriologo) {
    header('Location: nutriologos.php');
    exit;
}

// Registrar las visitas al perfil del nutriólogo
$stmt = $pdo->prepare("
    INSERT INTO visitas_perfil (nutriologo_id, visitante_id, ip_visitante, fecha_visita) 
    VALUES (?, ?, ?, GETDATE())
");
$stmt->execute([$nutriologo_id, $usuario['id'], $_SERVER['REMOTE_ADDR']]);


$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM PostsBlog WHERE autor_id = ? AND activo = 1");
$stmt->execute([$nutriologo_id]);
$total_posts = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM visitas_perfil WHERE nutriologo_id = ?");
$stmt->execute([$nutriologo_id]);
$total_visitas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];


$stmt = $pdo->prepare("
    SELECT p.*, c.nombre as categoria_nombre, c.color as categoria_color,
           (SELECT COUNT(*) FROM comentarios_blog WHERE post_id = p.id AND activo = 1) as num_comentarios,
           (SELECT COUNT(*) FROM likes_blog WHERE post_id = p.id) as num_likes
    FROM PostsBlog p
    LEFT JOIN CategoriasBlog c ON p.categoria_id = c.id
    WHERE p.autor_id = ? AND p.activo = 1
    ORDER BY p.fecha_publicacion DESC
    OFFSET 0 ROWS FETCH NEXT 5 ROWS ONLY
");
$stmt->execute([$nutriologo_id]);
$posts_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Procesar formulario de contacto
$mensaje_enviado = false;
$error_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_mensaje'])) {
    $nombre_contacto = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $email_contacto = isset($_POST['email']) ? trim($_POST['email']) : '';
    $telefono_contacto = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $mensaje_contacto = isset($_POST['mensaje']) ? trim($_POST['mensaje']) : '';
    
    if (empty($nombre_contacto) || empty($email_contacto) || empty($mensaje_contacto)) {
        $error_mensaje = 'Por favor completa todos los campos obligatorios';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO contactos (nutriologo_id, nombre, email, telefono, mensaje, fecha_contacto) 
                VALUES (?, ?, ?, ?, ?, GETDATE())
            ");
            $stmt->execute([$nutriologo_id, $nombre_contacto, $email_contacto, $telefono_contacto, $mensaje_contacto]);
            $mensaje_enviado = true;
        } catch(Exception $e) {
            $error_mensaje = 'Error al enviar el mensaje. Intenta de nuevo.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($nutriologo['nombre']); ?> - Bite & Balance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estiloperfilnutri.css">
    <link rel="shortcut icon" href="IMAGENESb/logo22.png" type="image/x-icon">
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-leaf"></i> Bite & Balance</h1>
        <div style="display: flex; gap: 15px;">
            <a href="pagina_inicio.php"><i class="fas fa-blog"></i> Blog</a>
            <a href="nutriologos.php"><i class="fas fa-user-md"></i> Nutriólogos</a>
            <a href="cerrarsesion.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>

    <div class="container">
        <a href="nutriologos.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver a Nutriólogos
        </a>

        <div class="profile-container">
           
            <aside class="profile-card">
                <div class="profile-photo">
                    <i class="fas fa-user-md"></i>
                </div>
                <h1 class="profile-name"><?php echo htmlspecialchars($nutriologo['nombre']); ?></h1>
                <p class="profile-specialty"><?php echo htmlspecialchars($nutriologo['especialidad'] ?? 'Nutriólogo'); ?></p>

                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_posts; ?></div>
                        <div class="stat-label">Posts</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $total_visitas; ?></div>
                        <div class="stat-label">Visitas</div>
                    </div>
                </div>

                <?php if ($nutriologo['cedula_profesional'] || $nutriologo['telefono'] || $nutriologo['email']): ?>
                <div class="profile-info">
                    <?php if ($nutriologo['cedula_profesional']): ?>
                    <div class="info-item">
                        <i class="fas fa-id-card"></i>
                        <span>Cédula: <?php echo htmlspecialchars($nutriologo['cedula_profesional']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($nutriologo['telefono']): ?>
                    <div class="info-item">
                        <i class="fas fa-phone"></i>
                        <span><?php echo htmlspecialchars($nutriologo['telefono']); ?></span>
                    </div>
                    <?php endif; ?>

                    <?php if ($nutriologo['email']): ?>
                    <div class="info-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($nutriologo['email']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if ($nutriologo['bio']): ?>
                <div class="profile-bio">
                    <strong style="color: #5a6f47; display: block; margin-bottom: 10px;">Sobre mí:</strong>
                    <?php echo nl2br(htmlspecialchars($nutriologo['bio'])); ?>
                </div>
                <?php endif; ?>
            </aside>

            
            <div class="main-content">
                <!-- Formulario de Contacto -->
                <section class="section">
                    <h2><i class="fas fa-envelope"></i> Contactar</h2>

                    <?php if ($mensaje_enviado): ?>
                    <div class="mensaje-success">
                        <i class="fas fa-check-circle"></i>
                        ¡Mensaje enviado exitosamente! El nutriólogo te contactará pronto.
                    </div>
                    <?php endif; ?>

                    <?php if ($error_mensaje): ?>
                    <div class="mensaje-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error_mensaje); ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" class="contact-form">
                        <div class="form-group">
                            <label for="nombre">Nombre completo *</label>
                            <input type="text" id="nombre" name="nombre" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Correo electrónico *</label>
                            <input type="email" id="email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="tel" id="telefono" name="telefono">
                        </div>

                        <div class="form-group">
                            <label for="mensaje">Mensaje *</label>
                            <textarea id="mensaje" name="mensaje" placeholder="Cuéntanos qué necesitas..." required></textarea>
                        </div>

                        <button type="submit" name="enviar_mensaje" class="btn-submit">
                            <i class="fas fa-paper-plane"></i> Enviar Mensaje
                        </button>
                    </form>
                </section>

                <!-- Posts Recientes -->
                <section class="section">
                    <h2><i class="fas fa-newspaper"></i> Posts Recientes</h2>

                    <?php if (count($posts_recientes) > 0): ?>
                        <?php foreach ($posts_recientes as $post): ?>
                        <div class="post-mini" onclick="window.location.href='ver_post.php?id=<?php echo $post['id']; ?>'">
                            <h3 class="post-mini-title"><?php echo htmlspecialchars($post['titulo']); ?></h3>
                            <div class="post-mini-meta">
                                <span><i class="far fa-calendar"></i> <?php 
                                    $fecha = $post['fecha_publicacion'];
                                    if ($fecha instanceof DateTime) {
                                        echo $fecha->format('d/m/Y');
                                    } else {
                                        echo date('d/m/Y', strtotime($fecha));
                                    }
                                ?></span>
                                <span><i class="far fa-heart"></i> <?php echo $post['num_likes']; ?></span>
                                <span><i class="far fa-comment"></i> <?php echo $post['num_comentarios']; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                        <p>Aún no hay posts publicados</p>
                    </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </div>
</body>
</html>