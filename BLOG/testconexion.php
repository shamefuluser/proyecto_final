<?php
try {
    $conn = new PDO("sqlsrv:Server=D5QL6CA;Database=biteandbalance", null, null);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo " Conexión exitosa";
} catch(PDOException $e) {
    echo " Error: " . $e->getMessage();
}
?>