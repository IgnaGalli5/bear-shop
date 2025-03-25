<?php
require_once 'config.php';

function conectarDB() {
    $conexion = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Verificar conexión
    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }
    
    // Establecer charset
    $conexion->set_charset("utf8");
    
    return $conexion;
}

// Función para ejecutar consultas
function query($sql) {
    $conexion = conectarDB();
    $resultado = $conexion->query($sql);
    
    if (!$resultado) {
        die("Error en la consulta: " . $conexion->error);
    }
    
    return $resultado;
}

// Función para obtener resultados como array
function obtenerResultados($sql) {
    $resultado = query($sql);
    $datos = [];
    
    while ($fila = $resultado->fetch_assoc()) {
        $datos[] = $fila;
    }
    
    return $datos;
}

// Función para escapar datos
function escapar($dato) {
    $conexion = conectarDB();
    return $conexion->real_escape_string($dato);
}