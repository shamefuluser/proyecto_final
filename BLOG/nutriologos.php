<?php
include 'verificar_sesion.php';
verificarSesion();

require_once 'config.php';
$pdo = getConexion();

$usuario = getUsuarioActual();

//SOLO nutriólogos RECOMENDADOS o con más contactos
$stmt = $pdo->query("
    SELECT TOP 9 u.id, u.nombre, u.email, p.especialidad, p.bio, p.foto_perfil,
           (SELECT COUNT(*) FROM PostsBlog WHERE autor_id = u.id AND activo = 1) as num_posts,
           (SELECT COUNT(*) FROM visitas_perfil WHERE nutriologo_id = u.id) as num_visitas,
           (SELECT COUNT(*) FROM contactos WHERE nutriologo_id = u.id) as num_contactos
    FROM usuarios u
    LEFT JOIN PerfilesNutriologo p ON u.id = p.usuario_id
    WHERE u.tipo_usuario = 'nutriologo' AND u.activo = 1 AND u.recomendado = 1
    ORDER BY num_contactos DESC, num_visitas DESC
");
$nutriologos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$imagen_default = 'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80';
?>
<!DOCTYPE html>
<html lang="es">
<head>
     <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nutriólogos Recomendados - Bite & Balance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilo_nutriologos.css">
    <link rel="shortcut icon" href="IMAGENESb/logo22.png" type="image/x-icon">

</head>
<body>
     <nav class="navbar">
        <h1><i class="fas fa-leaf"></i> Bite & Balance</h1>
        <div style="display: flex; gap: 15px;">
            <a href="../index.html"><i class="fas fa-home"></i> Inicio</a>
            <a href="pagina_inicio.php"><i class="fas fa-blog"></i> Blog</a>
            <a href="cerrarsesion.php"><i class="fas fa-sign-out-alt"></i> Salir</a>
        </div>
    </nav>


    <section class="hero">
        <h2>Nutriólogos Recomendados</h2>
        <p>Los profesionales más valorados por nuestra comunidad</p>
        <a href="todoslos_nutriologos.php" style="color: white; text-decoration: underline; margin-top: 15px; display: inline-block;">
            Ver todos los nutriólogos →
        </a>
    </section>

    <div class="container">
        <h2 class="section-title">Top Nutriólogos</h2>

        <div class="blog-container">
            <?php foreach ($nutriologos as $nutriologo): ?>
            <div class="card">
                <?php 
                $imagen = !empty($nutriologo['foto_perfil']) ? $nutriologo['foto_perfil'] : $imagen_default;
                ?>
                <img src="<?php echo htmlspecialchars($imagen); ?>" 
                     alt="<?php echo htmlspecialchars($nutriologo['nombre']); ?>"
                     onerror="this.src='<?php echo $imagen_default; ?>'">
                
                <div class="card-content">
                    <!-- Badge de recomendado -->
                    <span style="background: linear-gradient(135deg, #f39c12, #e67e22); color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; display: inline-block; margin-bottom: 10px;">
                        ⭐ Recomendado
                    </span>
                    
                    <h3><?php echo htmlspecialchars($nutriologo['nombre']); ?></h3>
                    
                    <?php if ($nutriologo['especialidad']): ?>
                    <p class="specialty">
                        <i class="fas fa-star"></i> <?php echo htmlspecialchars($nutriologo['especialidad']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <p>
                        <?php 
                        if ($nutriologo['bio']) {
                            echo strlen($nutriologo['bio']) > 100 
                                ? substr(htmlspecialchars($nutriologo['bio']), 0, 100) . '...' 
                                : htmlspecialchars($nutriologo['bio']);
                        } else {
                            echo 'Nutriólogo profesional especializado en bienestar y salud.';
                        }
                        ?>
                    </p>
                    
                    <div class="card-stats">
                        <span><i class="fas fa-newspaper"></i> <?php echo $nutriologo['num_posts']; ?> posts</span>
                        <span><i class="fas fa-users"></i> <?php echo $nutriologo['num_contactos']; ?> contactos</span>
                    </div>
                    
                    <a href="perfil_nutriologo.php?id=<?php echo $nutriologo['id']; ?>" class="btn">
                        <i class="fas fa-user-circle"></i> Ver Perfil y Contactar
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer>
        <p>© 2025 Bite & Balance. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
