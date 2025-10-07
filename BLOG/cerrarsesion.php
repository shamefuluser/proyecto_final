<?php
session_start();

// eliminar el token de la sesion si existe
if (isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    
    try {
        require_once 'config.php';
        $pdo = getConexion();
        
        // elimianar el de la base de datos
        $stmt = $pdo->prepare("DELETE FROM sesiones WHERE token = ?");
        $stmt->execute([$token]);
    } catch(Exception $e) {
       
    }
    
    // Eliminar la cookie
    setcookie('remember_token', '', time() - 3600, '/');
}


$_SESSION = array();


if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}


session_destroy();


header('Location: login.html');
exit;
?>