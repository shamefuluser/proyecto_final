<?php
require_once 'verificar_sesion.php';
verificarSesion(); 

require_once 'config.php';
$pdo = getConexion();

$usuario = getUsuarioActual();

// Obtener todos los nutriólogos activos
$stmt = $pdo->query("
    SELECT u.id, u.nombre, u.email, p.especialidad, p.bio, p.foto_perfil,
           (SELECT COUNT(*) FROM PostsBlog WHERE autor_id = u.id AND activo = 1) as num_posts,
           (SELECT COUNT(*) FROM visitas_perfil WHERE nutriologo_id = u.id) as num_visitas
    FROM usuarios u
    LEFT JOIN PerfilesNutriologo p ON u.id = p.usuario_id
    WHERE u.tipo_usuario = 'nutriologo' AND u.activo = 1
    ORDER BY u.nombre
");
$nutriologos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$imagenes = [
    'https://images.unsplash.com/photo-1622253692010-333f2da6031d?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1552058544-f2b08422138a?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1554151228-14d9def656e4?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=800&q=80',
    'https://imgs.search.brave.com/bCBURAw5uYtpUvP9LJsm87uVyxT9gI62ygTbu448dgc/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pLnBp/bmltZy5jb20vb3Jp/Z2luYWxzL2NlLzYz/LzQ3L2NlNjM0NzU1/NjJmOTYwZWRlMzUx/YzI1MzM0MTVhZGM1/LmpwZw',
    'https://imgs.search.brave.com/ZZ6ILaDRu2Smijz0w09CO_jfTpUx7mYYCrMg2rYZt6Q/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9tZWRp/YS5nZXR0eWltYWdl/cy5jb20vaWQvMTIw/NDQzNjI4OC9lcy9m/b3RvL2F0cmFjdGl2/YS1tJUMzJUE5ZGlj/by1oZW1icmEtZGUt/bWVkaW8tYWR1bHRv/LWNvbi1zb25yaXNh/cy1kZS1lc3RldG9z/Y29waW8tcGFyYS1s/YS1jJUMzJUExbWFy/YS5qcGc_cz02MTJ4/NjEyJnc9MCZrPTIw/JmM9eW5pS3owamhK/OGJYV1pKSFppN2d6/VTg1TldrNlZGS3hw/SHBGbVM5ZFVacz0',
    'https://imgs.search.brave.com/cL3mZm9_2xVMc02u2UTyuoZ8zBxwkXkefenB9lhgRxk/rs:fit:860:0:0:0/g:ce/aHR0cHM6Ly9pbWFn/ZXMudW5zcGxhc2gu/Y29tL3Bob3RvLTE2/MjIyNTM2OTIwMTAt/MzMzZjJkYTYwMzFk/P2ZtPWpwZyZxPTYw/Jnc9MzAwMCZpeGxp/Yj1yYi00LjEuMCZp/eGlkPU0zd3hNakEz/ZkRCOE1IeHpaV0Z5/WTJoOE5ueDhaRzlq/ZEc5eWZHVnVmREI4/ZkRCOGZId3c',
    'https://images.unsplash.com/photo-1594824476967-48c8b964273f?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=800&q=80',
    'https://images.unsplash.com/photo-1607746882042-944635dfe10e?auto=format&fit=crop&w=800&q=80'
];
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
        <h2>Encuentra tu Nutriólogo Ideal</h2>
        <p>Consejos, profesionales y bienestar en un solo lugar</p>
    </section>

    <div class="container">
        <h2 class="section-title">Nutriólogos Recomendados</h2>

        <div class="blog-container">
            <?php 
            $index = 0;
            foreach ($nutriologos as $nutriologo): 
                
                $imagen = $imagenes[$index % count($imagenes)];
                $index++;
            ?>
            <div class="card">
                <img src="<?php echo $imagen; ?>" alt="<?php echo htmlspecialchars($nutriologo['nombre']); ?>">
                <div class="card-content">
                    <h3><?php echo htmlspecialchars($nutriologo['nombre']); ?></h3>
                    <?php if ($nutriologo['especialidad']): ?>
                    <p class="specialty">
                        <i class="fas fa-star"></i> <?php echo htmlspecialchars($nutriologo['especialidad']); ?>
                    </p>
                    <?php endif; ?>
                    <p>
                        <?php 
                        if ($nutriologo['bio']) {
                            echo strlen($nutriologo['bio']) > 100 ? substr(htmlspecialchars($nutriologo['bio']), 0, 100) . '...' : htmlspecialchars($nutriologo['bio']);
                        } else {
                            echo 'Nutriólogo profesional especializado en bienestar y salud.';
                        }
                        ?>
                    </p>
                    <div class="card-stats">
                        <span><i class="fas fa-newspaper"></i> <?php echo $nutriologo['num_posts']; ?> posts</span>
                        <span><i class="fas fa-eye"></i> <?php echo $nutriologo['num_visitas']; ?> visitas</span>
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