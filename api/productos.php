<?php
require_once '../includes/db.php';

// Establecer cabeceras para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Obtener todos los productos
$productos = obtenerResultados("SELECT * FROM productos ORDER BY id");

// Transformar datos para que coincidan con el formato esperado por el frontend
$productosFormateados = [];

foreach ($productos as $producto) {
    // Convertir características de texto a array
    $caracteristicas = explode("\n", $producto['caracteristicas']);
    
    $productosFormateados[] = [
        'id' => (int)$producto['id'],
        'name' => $producto['nombre'],
        'price' => (float)$producto['precio'],
        'installments' => (int)$producto['cuotas'],
        'installmentPrice' => (float)$producto['precio_cuota'],
        'image' => $producto['imagen'],
        'category' => $producto['categoria'],
        'description' => $producto['descripcion'],
        'features' => $caracteristicas,
        'usage' => $producto['modo_uso'],
        'rating' => (float)$producto['calificacion'],
        'ratingCount' => (int)$producto['num_calificaciones']
    ];
}

// Devolver JSON
echo json_encode($productosFormateados);