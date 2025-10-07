<?php


include 'paginahome.php'; // Carga los datos
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Bite & Balance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilo_inicioBlog.css">
      <link rel="shortcut icon" href="IMAGENESb/logo22.png" type="image/x-icon">
</head>
<body>
    <nav class="navbar">
        <div class="navbar-left">
            <h1><i class="fas fa-leaf"></i> Bite & Balance - Blog</h1>
            <div class="navbar-menu">
                <a href="../index.html"><i class="fas fa-home"></i> Inicio</a>
                <?php if ($es_nutriologo): ?>
                <a href="panel_nutriologo.php"><i class="fas fa-chart-line"></i> Mi Panel</a>
                <?php endif; ?>
                <a href="nutriologos.php"><i class="fas fa-user-md"></i> Nutriólogos</a>
                <a href="../emplopro/app.html"><i class="fas fa-mobile-alt"></i> Aplicaciones</a> 
            </div>
        </div>
        <div class="navbar-right">
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                <span class="user-badge"><?php echo $es_nutriologo ? 'Nutriólogo' : 'Visitante'; ?></span>
            </div>
            <a href="cerrarsesion.php" style="color: white; text-decoration: none;">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>
    </nav>

    <div class="container">
        <aside class="sidebar">
            <?php if ($es_nutriologo): ?>
            <div class="sidebar-section">
                <button class="btn-nuevo-post" onclick="window.location.href='nuevo_post.php'">
                    <i class="fas fa-plus-circle"></i> Nuevo Post
                </button>
            </div>
            <?php endif; ?>

            <div class="sidebar-section">
                <h3><i class="fas fa-search"></i> Buscar</h3>
                <form class="search-box" method="GET" action="">
                    <input type="text" name="buscar" placeholder="Buscar posts..." value="<?php echo htmlspecialchars($busqueda); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>

            <div class="sidebar-section">
                <h3><i class="fas fa-folder"></i> Categorías</h3>
                <div class="categoria-item <?php echo $categoria_filtro == 0 ? 'active' : ''; ?>" 
                     onclick="window.location.href='pagina_inicio.php'">
                    <div class="categoria-icon" style="background: #95a5a6; color: white;">
                        <i class="fas fa-th"></i>
                    </div>
                    <span>Todas</span>
                </div>
                <?php foreach ($categorias as $cat): ?>
                <div class="categoria-item <?php echo $categoria_filtro == $cat['id'] ? 'active' : ''; ?>" 
                     onclick="window.location.href='pagina_inicio.php?categoria=<?php echo $cat['id']; ?>'">
                    <div class="categoria-icon" style="background: <?php echo htmlspecialchars($cat['color']); ?>; color: white;">
                        <i class="<?php echo htmlspecialchars($cat['icono']); ?>"></i>
                    </div>
                    <span><?php echo htmlspecialchars($cat['nombre']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </aside>

        <main class="main-content">
            <?php if (count($posts) > 0): ?>
                <?php foreach ($posts as $post): ?>
                <article class="post-card">
                    <div class="post-header">
                        <div class="post-author">
                            <div class="author-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="author-info">
                                <h4>
                                    <?php echo htmlspecialchars($post['autor_nombre']); ?>
                                    <?php if ($post['autor_tipo'] === 'nutriologo'): ?>
                                    <span class="author-badge">Nutriólogo</span>
                                    <?php endif; ?>
                                </h4>
                                <div class="author-meta">
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
                        <?php if ($post['categoria_nombre']): ?>
                        <span class="post-categoria" style="background: <?php echo htmlspecialchars($post['categoria_color']); ?>">
                            <?php echo htmlspecialchars($post['categoria_nombre']); ?>
                        </span>
                        <?php endif; ?>
                    </div>

                    <h2 class="post-title" onclick="window.location.href='ver_post.php?id=<?php echo $post['id']; ?>'">
                        <?php echo htmlspecialchars($post['titulo']); ?>
                    </h2>

                    <div class="post-content">
                        <?php 
                        $contenido = htmlspecialchars($post['contenido']);
                        echo nl2br(substr($contenido, 0, 300)) . (strlen($contenido) > 300 ? '...' : ''); 
                        ?>
                    </div>

                    <div class="post-footer">
                        <div class="post-stats">
                            <div class="post-stat">
                                <i class="fas fa-heart"></i>
                                <span><?php echo $post['num_likes']; ?> likes</span>
                            </div>
                            <div class="post-stat">
                                <i class="fas fa-comment"></i>
                                <span><?php echo $post['num_comentarios']; ?> comentarios</span>
                            </div>
                        </div>
                        <div class="post-actions">
                            <a href="ver_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">
                                <i class="fas fa-book-open"></i> Leer más
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>No se encontraron posts</h3>
                <p>Intenta con otra búsqueda o categoría</p>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>