<?php
// Parámetros de conexión directos (sin incluir otros archivos)
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bear_shop';

// Intentar conexión
try {
    $conexion = new mysqli($host, $user, $pass, $dbname);
    
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    echo "¡Conexión exitosa a la base de datos!";
    
    // Intentar una consulta simple
    $resultado = $conexion->query("SHOW TABLES");
    
    echo "<p>Tablas en la base de datos:</p>";
    echo "<ul>";
    while ($fila = $resultado->fetch_row()) {
        echo "<li>" . $fila[0] . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>