<?php
// Establecer cabeceras para JSON y CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Añadir estas líneas para evitar el caché
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

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
  $productos = obtenerResultados("
SELECT p.*, 
       pr.id as promocion_id, 
       pr.nombre as nombre_promocion, 
       pr.tipo as tipo_promocion, 
       pr.valor as valor_promocion,
       CASE 
           WHEN pr.id IS NOT NULL AND pr.activa = 1 
                AND pr.fecha_inicio <= CURDATE() 
                AND pr.fecha_fin >= CURDATE() 
           THEN 
               CASE 
                   WHEN pr.tipo = 'porcentaje' THEN p.precio - (p.precio * pr.valor / 100)
                   WHEN pr.tipo = 'monto_fijo' THEN p.precio - pr.valor
                   ELSE p.precio_promocion
               END
           ELSE p.precio_promocion
       END as precio_promocion
FROM productos p
LEFT JOIN promociones pr ON p.promocion_id = pr.id
ORDER BY p.id
");
  
  // Obtener la promoción de descuento en efectivo (si existe)
  $descuento_efectivo_query = "
      SELECT valor FROM promociones 
      WHERE nombre = 'Descuento Efectivo' 
      AND activa = 1 
      AND fecha_inicio <= CURDATE() 
      AND fecha_fin >= CURDATE()
      LIMIT 1
  ";
  $descuento_efectivo_result = obtenerResultados($descuento_efectivo_query);
  
  // Valor por defecto del descuento en efectivo (10% si no hay promoción específica)
  $descuento_efectivo = 10;
  
  // Si existe una promoción de descuento en efectivo, usar ese valor
  if (!empty($descuento_efectivo_result)) {
      $descuento_efectivo = (float)$descuento_efectivo_result[0]['valor'];
  }

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
      $nombre_promocion = null;
      $tipo_promocion = null;
      $valor_promocion = null;
      
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
              $nombre_promocion = $producto['nombre_promocion'];
              $tipo_promocion = $producto['tipo_promocion'];
              $valor_promocion = $producto['valor_promocion'];
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
          'nombre_promocion' => $nombre_promocion,
          'tipo_promocion' => $tipo_promocion,
          'valor_promocion' => $valor_promocion,
          'installments' => $cuotas,
          'installmentPrice' => (float)$precio_cuota,
          'image' => isset($producto['imagen']) ? $producto['imagen'] : 'productos/default.jpg',
          'category' => isset($producto['categoria']) ? $producto['categoria'] : '',
          'description' => isset($producto['descripcion']) ? $producto['descripcion'] : '',
          'features' => $caracteristicas,
          'usage' => isset($producto['modo_uso']) ? $producto['modo_uso'] : '',
          'rating' => isset($producto['calificacion']) ? (float)$producto['calificacion'] : 5.0,
          'ratingCount' => isset($producto['num_calificaciones']) ? (int)$producto['num_calificaciones'] : 0,
          // Mantener el porcentaje de descuento en efectivo para el carrito
          'cashDiscountPercent' => (float)$descuento_efectivo
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
      "),
      'descuento_efectivo' => $descuento_efectivo
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

