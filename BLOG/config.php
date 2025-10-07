<?php

//configuracion de la base de datos
define('DB_SERVER', 'D5QL6CA');
define('DB_NAME', 'bite_balance');


function getConexion() {
    try {
        $dsn = "sqlsrv:Server=" . DB_SERVER . ";Database=" . DB_NAME;
        $pdo = new PDO($dsn, null, null); 
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
    }
}

// Función para limpiar datos
function limpiarDato($dato) {
    return htmlspecialchars(strip_tags(trim($dato)), ENT_QUOTES, 'UTF-8');
}
?>