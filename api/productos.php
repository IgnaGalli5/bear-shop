<?php
// Establecer cabeceras para JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Si es una solicitud OPTIONS (preflight), terminar aquí
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Desactivar la visualización de errores
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once '../includes/db.php';

    // Obtener todos los productos
    $productos = obtenerResultados("SELECT * FROM productos ORDER BY id");

    // Transformar datos para que coincidan con el formato esperado por el frontend
    $productosFormateados = [];

    foreach ($productos as $producto) {
        // Convertir características de texto a array
        $caracteristicas = [];
        if (isset($producto['caracteristicas']) && !empty($producto['caracteristicas'])) {
            $caracteristicas = explode("\n", $producto['caracteristicas']);
        }
        
        // Determinar si hay precio promocional activo
        $precio_mostrar = $producto['precio'];
        $precio_original = null;
        $enPromocion = false;
        
        if (isset($producto['promocion_id']) && !empty($producto['promocion_id'])) {
            // Verificar si la promoción está activa y vigente
            $promocion = obtenerResultados("
                SELECT * FROM promociones 
                WHERE id = " . intval($producto['promocion_id']) . " 
                AND activa = 1 
                AND fecha_inicio <= CURDATE() 
                AND fecha_fin >= CURDATE()
            ");
            
            if (!empty($promocion)) {
                $enPromocion = true;
                $precio_original = $producto['precio'];
                $precio_mostrar = isset($producto['precio_promocion']) ? $producto['precio_promocion'] : $precio_mostrar;
            }
        }
        
        // Establecer valores para cuotas
        $cuotas = isset($producto['cuotas']) ? (int)$producto['cuotas'] : 1;
        $precio_cuota = $precio_mostrar / $cuotas;
        
        $productosFormateados[] = [
            'id' => (int)$producto['id'],
            'name' => $producto['nombre'],
            'price' => (float)$precio_mostrar,
            'originalPrice' => $enPromocion ? (float)$precio_original : null,
            'onSale' => $enPromocion,
            'installments' => $cuotas,
            'installmentPrice' => (float)$precio_cuota,
            'image' => isset($producto['imagen']) ? $producto['imagen'] : 'productos/default.jpg',
            'category' => isset($producto['categoria']) ? $producto['categoria'] : '',
            'description' => isset($producto['descripcion']) ? $producto['descripcion'] : '',
            'features' => $caracteristicas,
            'usage' => isset($producto['modo_uso']) ? $producto['modo_uso'] : '',
            'rating' => isset($producto['calificacion']) ? (float)$producto['calificacion'] : 5.0,
            'ratingCount' => isset($producto['num_calificaciones']) ? (int)$producto['num_calificaciones'] : 0
        ];
    }

    // OPCIÓN 1: Solo devolver los productos (recomendado para producción)
    echo json_encode($productosFormateados, JSON_UNESCAPED_UNICODE);
    
    // OPCIÓN 2: Devolver productos con información de depuración (solo para desarrollo)
    // Descomentar esto y comentar la línea anterior si necesitas depurar
    /*
    $debug_info = [
        'promociones_activas' => obtenerResultados("
            SELECT * FROM promociones 
            WHERE activa = 1 
            AND fecha_inicio <= CURDATE() 
            AND fecha_fin >= CURDATE()
        "),
        'productos_en_promocion' => obtenerResultados("
            SELECT id, nombre, precio, precio_promocion, promocion_id 
            FROM productos 
            WHERE promocion_id IS NOT NULL
        ")
    ];
    
    $respuesta = [
        'productos' => $productosFormateados,
        'debug' => $debug_info
    ];
    
    echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
    */
    
} catch (Exception $e) {
    // Devolver error en formato JSON
    echo json_encode([
        'error' => true,
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>