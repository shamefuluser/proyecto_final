<?php
session_start();
include_once 'config.php';

function verificarSesion($tipo_requerido = null) {
    // Verificar si hay sesión activa
    if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_tipo'])) {
        if ($tipo_requerido && $_SESSION['usuario_tipo'] !== $tipo_requerido) {
            header('Location: login.html');
            exit;
        }
        return true;
    }
    
    
    if (isset($_COOKIE['remember_token'])) {
        try {
            $pdo = getConexion();
            $token = $_COOKIE['remember_token'];
            
            $sql = "SELECT s.*, u.nombre, u.email, u.tipo_usuario 
                    FROM sesiones s
                    JOIN usuarios u ON s.usuario_id = u.id
                    WHERE s.token = ? AND s.fecha_expiracion > GETDATE()";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$token]);
            $sesion = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($sesion) {
                $_SESSION['usuario_id'] = $sesion['usuario_id'];
                $_SESSION['usuario_nombre'] = $sesion['nombre'];
                $_SESSION['usuario_email'] = $sesion['email'];
                $_SESSION['usuario_tipo'] = $sesion['tipo_usuario'];
                $_SESSION['login_time'] = time();
                
                if ($tipo_requerido && $sesion['tipo_usuario'] !== $tipo_requerido) {
                    header('Location: login.html');
                    exit;
                }
                
                return true;
            }
        } catch(Exception $e) {
            // Continuar sin sesión
        }
    }
    
    // No hay sesión válida
    header('Location: login.html');
    exit;
}

function getUsuarioActual() {
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }
    
    return [
        'id' => $_SESSION['usuario_id'],
        'nombre' => $_SESSION['usuario_nombre'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'tipo' => $_SESSION['usuario_tipo'] ?? ''
    ];
}

function esNutriologo() {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'nutriologo';
}

function esVisitante() {
    return isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'visitante';
}
?>