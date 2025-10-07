<?php
include 'verificar_sesion.php';
verificarSesion('nutriologo'); 

require_once 'config.php';
$pdo = getConexion();

$usuario_id = $_SESSION['usuario_id'];
$usuario_nombre = $_SESSION['usuario_nombre'];

// Obtener estadísticas
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM visitas_perfil WHERE nutriologo_id = ?");
$stmt->execute([$usuario_id]);
$total_visitas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contactos WHERE nutriologo_id = ?");
$stmt->execute([$usuario_id]);
$total_contactos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM contactos WHERE nutriologo_id = ? AND estado = 'nuevo'");
$stmt->execute([$usuario_id]);
$contactos_nuevos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Obtener visitas recientes
$stmt = $pdo->prepare("
    SELECT v.*, u.nombre as visitante_nombre, u.email as visitante_email
    FROM visitas_perfil v
    LEFT JOIN usuarios u ON v.visitante_id = u.id
    WHERE v.nutriologo_id = ?
    ORDER BY v.fecha_visita DESC
    OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY
");
$stmt->execute([$usuario_id]);
$visitas_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener contactos recientes
$stmt = $pdo->prepare("
    SELECT * FROM contactos 
    WHERE nutriologo_id = ? 
    ORDER BY fecha_contacto DESC 
    OFFSET 0 ROWS FETCH NEXT 10 ROWS ONLY
");
$stmt->execute([$usuario_id]);
$contactos_recientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Nutriólogo - Bite & Balance</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="estilo_panelnutriologo.css">
    <link rel="shortcut icon" href="IMAGENESb/logo22.png" type="image/x-icon">
</head>
<body>
    <nav class="navbar">
        <h1><i class="fas fa-leaf"></i> Bite & Balance</h1>
        <div class="navbar-right">
            <span><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($usuario_nombre); ?></span>
            <a href="pagina_inicio.php"><i class="fas fa-blog"></i> Blog</a>
            <a href="cerrarsesion.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </nav>

    <div class="container">
        <div class="welcome-section">
            <h2>¡Bienvenido, <?php echo htmlspecialchars($usuario_nombre); ?>!</h2>
            <p>Este es tu panel de control. Aquí puedes ver tus estadísticas y gestionar tus contactos.</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-eye"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_visitas; ?></h3>
                    <p>Visitas al Perfil</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $total_contactos; ?></h3>
                    <p>Total Contactos</p>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="stat-info">
                    <h3><?php echo $contactos_nuevos; ?></h3>
                    <p>Contactos Nuevos</p>
                </div>
            </div>
        </div>

        <div class="section">
            <h3><i class="fas fa-address-book"></i> Contactos Recientes</h3>
            <?php if (count($contactos_recientes) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Mensaje</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contactos_recientes as $contacto): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($contacto['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($contacto['email']); ?></td>
                            <td><?php echo htmlspecialchars($contacto['telefono'] ?? 'N/A'); ?></td>
                            <td><?php echo substr(htmlspecialchars($contacto['mensaje']), 0, 50) . '...'; ?></td>
                            <td><?php 
                                $fecha = $contacto['fecha_contacto'];
                                if ($fecha instanceof DateTime) {
                                    echo $fecha->format('d/m/Y');
                                } else {
                                    echo date('d/m/Y', strtotime($fecha));
                                }
                            ?></td>
                            <td>
                                <span class="badge <?php echo $contacto['estado']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $contacto['estado'])); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No tienes contactos aún</p>
            </div>
            <?php endif; ?>
        </div>

        <div class="section">
            <h3><i class="fas fa-history"></i> Visitas Recientes al Perfil</h3>
            <?php if (count($visitas_recientes) > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Visitante</th>
                            <th>IP</th>
                            <th>Fecha</th>
                            <th>Duración</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visitas_recientes as $visita): ?>
                        <tr>
                            <td>
                                <?php 
                                if (isset($visita['visitante_nombre']) && $visita['visitante_nombre']) {
                                    echo htmlspecialchars($visita['visitante_nombre']);
                                } else {
                                    echo '<i class="fas fa-user-secret"></i> Anónimo';
                                }
                                ?>
                            </td>
                            <td><?php echo htmlspecialchars($visita['ip_visitante']); ?></td>
                            <td><?php 
                                $fecha = $visita['fecha_visita'];
                                if ($fecha instanceof DateTime) {
                                    echo $fecha->format('d/m/Y H:i');
                                } else {
                                    echo date('d/m/Y H:i', strtotime($fecha));
                                }
                            ?></td>
                            <td><?php echo $visita['duracion_segundos']; ?>s</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-chart-line"></i>
                <p>No hay visitas registradas aún</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>