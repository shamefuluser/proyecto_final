<?php
include 'verificar_sesion.php';
verificarSesion();

require_once 'config.php';
$pdo = getConexion();

$usuario = getUsuarioActual();
$es_nutriologo = esNutriologo();
$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($post_id <= 0) {
    header('Location: pagina_inicio.php');
    exit;
}

// Incrementar vistas
$stmt = $pdo->prepare("UPDATE PostsBlog SET vistas = vistas + 1 WHERE id = ?");
$stmt->execute([$post_id]);

// Obtener el post
$stmt = $pdo->prepare("
    SELECT p.*, u.nombre as autor_nombre, u.tipo_usuario as autor_tipo, 
           c.nombre as categoria_nombre, c.color as categoria_color,
           (SELECT COUNT(*) FROM likes_blog WHERE post_id = p.id) as num_likes,
           (SELECT COUNT(*) FROM likes_blog WHERE post_id = p.id AND usuario_id = ?) as user_liked
    FROM PostsBlog p
    JOIN usuarios u ON p.autor_id = u.id
    LEFT JOIN CategoriasBlog c ON p.categoria_id = c.id
    WHERE p.id = ? AND p.activo = 1
");

$stmt->execute([$usuario['id'], $post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$post) {
    header('Location: pagina_inicio.php');
    exit;
}

// Obtener comentarios principales (sin padre)
$stmt = $pdo->prepare("
    SELECT c.*, u.nombre as autor_nombre, u.tipo_usuario as autor_tipo,
           (SELECT COUNT(*) FROM likes_blog WHERE comentario_id = c.id) as num_likes,
           (SELECT COUNT(*) FROM comentarios_blog WHERE comentario_padre_id = c.id AND activo = 1) as num_respuestas
    FROM comentarios_blog c
    JOIN usuarios u ON c.autor_id = u.id
    WHERE c.post_id = ? AND c.comentario_padre_id IS NULL AND c.activo = 1
    ORDER BY c.fecha_comentario DESC
");
$stmt->execute([$post_id]);
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para obtener respuestas de un comentario
function obtenerRespuestas($pdo, $comentario_id) {
    $stmt = $pdo->prepare("
        SELECT c.*, u.nombre as autor_nombre, u.tipo_usuario as autor_tipo
        FROM comentarios_blog c
        JOIN usuarios u ON c.autor_id = u.id
        WHERE c.comentario_padre_id = ? AND c.activo = 1
        ORDER BY c.fecha_comentario ASC
    ");
    $stmt->execute([$comentario_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['titulo']); ?> - Bite & Balance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="IMAGENESb/logo22.png" type="image/x-icon">
    <link rel="stylesheet" href="estilo_verposts.css">
    
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-leaf"></i> Bite & Balance</h1>
        <div style="display: flex; gap: 15px;">
            <a href="pagina_inicio.php"><i class="fas fa-blog"></i> Blog</a>
            <?php if ($es_nutriologo): ?>
            <a href="panel_nutriologo.php"><i class="fas fa-chart-line"></i> Panel</a>
            <?php endif; ?>
            <a href="cerrarsesion.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>

    <div class="container">
        <a href="pagina_inicio.php" class="back-button">
            <i class="fas fa-arrow-left"></i> Volver al Blog
        </a>

        <article class="post-container">
            <div class="post-header">
                <?php if ($post['categoria_nombre']): ?>
                <span class="post-categoria" style="background: <?php echo htmlspecialchars($post['categoria_color']); ?>">
                    <?php echo htmlspecialchars($post['categoria_nombre']); ?>
                </span>
                <?php endif; ?>

                <h1 class="post-title"><?php echo htmlspecialchars($post['titulo']); ?></h1>

                <div class="post-meta">
                    <div class="author-info">
                        <div class="author-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <div>
                            <strong><?php echo htmlspecialchars($post['autor_nombre']); ?></strong>
                            <?php if ($post['autor_tipo'] === 'nutriologo'): ?>
                            <span class="author-badge">Nutriólogo</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="post-stats">
                        <span><i class="far fa-clock"></i> <?php 
                            $fecha = $post['fecha_publicacion'];
                            if ($fecha instanceof DateTime) {
                                echo $fecha->format('d/m/Y H:i');
                            } else {
                                echo date('d/m/Y H:i', strtotime($fecha));
                            }
                        ?></span>
                        <span><i class="fas fa-eye"></i> <?php echo $post['vistas']; ?> vistas</span>
                    </div>
                </div>
            </div>

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($post['contenido'])); ?>
            </div>

            <div class="post-actions">
                <button class="action-btn <?php echo $post['user_liked'] > 0 ? 'liked' : ''; ?>" 
                        onclick="toggleLike('post', <?php echo $post['id']; ?>)">
                    <i class="fas fa-heart"></i>
                    <span id="likes-count"><?php echo $post['num_likes']; ?></span> Me gusta
                </button>
                <button class="action-btn report-btn" onclick="reportarContenido('post', <?php echo $post['id']; ?>)">
                    <i class="fas fa-flag"></i> Reportar
                </button>
            </div>
        </article>

        <div class="comments-section">
            <div class="comments-header">
                <h3><i class="fas fa-comments"></i> Comentarios (<?php echo count($comentarios); ?>)</h3>
            </div>

            <!-- Formulario para nuevo comentario -->
            <div class="comment-form">
                <h4>Deja tu comentario</h4>
                <form id="formComentario" onsubmit="enviarComentario(event, <?php echo $post_id; ?>, null)">
                    <textarea id="comentario-texto" name="contenido" placeholder="Escribe tu comentario..." required></textarea>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Publicar Comentario
                    </button>
                </form>
            </div>

            <!-- Lista de comentarios -->
            <?php if (count($comentarios) > 0): ?>
                <?php foreach ($comentarios as $comentario): ?>
                <div class="comment" id="comentario-<?php echo $comentario['id']; ?>">
                    <div class="comment-header">
                        <div class="comment-author">
                            <div class="comment-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <strong><?php echo htmlspecialchars($comentario['autor_nombre']); ?></strong>
                                <?php if ($comentario['autor_tipo'] === 'nutriologo'): ?>
                                <span class="author-badge">Nutriólogo</span>
                                <?php endif; ?>
                                <div style="color: #999; font-size: 0.85rem;">
                                    <?php 
                                        $fecha = $comentario['fecha_comentario'];
                                        if ($fecha instanceof DateTime) {
                                            echo $fecha->format('d/m/Y H:i');
                                        } else {
                                            echo date('d/m/Y H:i', strtotime($fecha));
                                        }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="comment-content">
                        <?php echo nl2br(htmlspecialchars($comentario['contenido'])); ?>
                    </div>

                    <div class="comment-actions">
                        <button class="comment-action-btn" onclick="toggleLike('comentario', <?php echo $comentario['id']; ?>)">
                            <i class="far fa-heart"></i> <?php echo $comentario['num_likes']; ?>
                        </button>
                        <button class="comment-action-btn" onclick="mostrarFormRespuesta(<?php echo $comentario['id']; ?>)">
                            <i class="fas fa-reply"></i> Responder
                        </button>
                        <button class="comment-action-btn" onclick="reportarContenido('comentario', <?php echo $comentario['id']; ?>)">
                            <i class="fas fa-flag"></i> Reportar
                        </button>
                    </div>

                    <!-- Formulario para responder -->
                    <div class="form-respuesta" id="form-respuesta-<?php echo $comentario['id']; ?>">
                        <form onsubmit="enviarComentario(event, <?php echo $post_id; ?>, <?php echo $comentario['id']; ?>)">
                            <textarea name="contenido" placeholder="Escribe tu respuesta..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 2px solid #e0e0e0; min-height: 80px;"></textarea>
                            <div style="margin-top: 10px; display: flex; gap: 10px;">
                                <button type="submit" class="btn btn-primary btn-sm" style="padding: 8px 16px; font-size: 0.9rem;">
                                    <i class="fas fa-paper-plane"></i> Responder
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" onclick="ocultarFormRespuesta(<?php echo $comentario['id']; ?>)" style="padding: 8px 16px; font-size: 0.9rem;">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Respuestas -->
                    <?php if ($comentario['num_respuestas'] > 0): ?>
                    <div class="respuestas">
                        <?php 
                        $respuestas = obtenerRespuestas($pdo, $comentario['id']);
                        foreach ($respuestas as $respuesta): 
                        ?>
                        <div class="respuesta">
                            <div class="comment-author" style="margin-bottom: 10px;">
                                <div class="comment-avatar" style="width: 30px; height: 30px; font-size: 0.9rem;">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div>
                                    <strong><?php echo htmlspecialchars($respuesta['autor_nombre']); ?></strong>
                                    <?php if ($respuesta['autor_tipo'] === 'nutriologo'): ?>
                                    <span class="author-badge" style="font-size: 0.65rem; padding: 2px 8px;">Nutriólogo</span>
                                    <?php endif; ?>
                                    <div style="color: #999; font-size: 0.8rem;">
                                        <?php 
                                            $fecha = $respuesta['fecha_comentario'];
                                            if ($fecha instanceof DateTime) {
                                                echo $fecha->format('d/m/Y H:i');
                                            } else {
                                                echo date('d/m/Y H:i', strtotime($fecha));
                                            }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <div style="color: #555; font-size: 0.95rem;">
                                <?php echo nl2br(htmlspecialchars($respuesta['contenido'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-comments">
                <i class="fas fa-comments"></i>
                <p>Aún no hay comentarios. ¡Sé el primero en comentar!</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function mostrarFormRespuesta(comentarioId) {
            const form = document.getElementById('form-respuesta-' + comentarioId);
            form.classList.add('active');
        }

        function ocultarFormRespuesta(comentarioId) {
            const form = document.getElementById('form-respuesta-' + comentarioId);
            form.classList.remove('active');
        }

        function enviarComentario(event, postId, comentarioPadreId) {
            event.preventDefault();
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('post_id', postId);
            if (comentarioPadreId) {
                formData.append('comentario_padre_id', comentarioPadreId);
            }

            fetch('procesar_comentario.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('¡Comentario publicado!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al publicar comentario');
            });
        }

        function toggleLike(tipo, id) {
            fetch('dar_like.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'tipo=' + tipo + '&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }

        function reportarContenido(tipo, id) {
            const motivo = prompt('¿Por qué deseas reportar este contenido?');
            if (motivo && motivo.trim() !== '') {
                fetch('reportar_contenido.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'tipo=' + tipo + '&id=' + id + '&motivo=' + encodeURIComponent(motivo)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Reporte enviado. Será revisado por el equipo.');
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al enviar reporte');
                });
            }
        }
    </script>
</body>
</html>