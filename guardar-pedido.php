<?php
// Permitir solicitudes CORS (necesario para peticiones AJAX)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Manejar solicitudes OPTIONS (pre-flight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'includes/config.php';
require_once 'includes/db.php';

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

// Obtener datos del pedido desde la petición
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    exit;
}

try {
    // Extraer datos del pedido
    $cliente_nombre = isset($data['nombre']) ? escapar($data['nombre']) : '';
    $cliente_email = isset($data['email']) ? escapar($data['email']) : '';
    $metodo_pago = isset($data['metodo_pago']) ? escapar($data['metodo_pago']) : 'Efectivo';
    $total = isset($data['total']) ? (float)$data['total'] : 0;
    $items = isset($data['items']) ? $data['items'] : [];
    
    // Validar datos mínimos
    if (empty($items) || $total <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Datos de pedido incompletos']);
        exit;
    }
    
    // Insertar pedido en la base de datos
    $fecha = date('Y-m-d H:i:s');
    $estado = 'completado';
    $cliente_id = 1; // Cliente por defecto si no hay sistema de usuarios
    
    $sql_pedido = "INSERT INTO pedidos (cliente_id, fecha, estado, total, metodo_pago, direccion_envio, notas) 
                   VALUES ($cliente_id, '$fecha', '$estado', $total, '$metodo_pago', '$cliente_nombre', '$cliente_email')";
    
    if (!query($sql_pedido)) {
        throw new Exception("Error al insertar pedido: " . mysqli_error(conectarDB()));
    }
    
    // Obtener ID del pedido insertado
    $pedido_id = mysqli_insert_id(conectarDB());
    
    // Insertar items del pedido
    foreach ($items as $item) {
        $producto_id = (int)$item['id'];
        $cantidad = (int)$item['quantity'];
        $precio_unitario = (float)$item['price'];
        $subtotal = $precio_unitario * $cantidad;
        
        $sql_item = "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                     VALUES ($pedido_id, $producto_id, $cantidad, $precio_unitario, $subtotal)";
        
        if (!query($sql_item)) {
            // Registrar error pero continuar con los demás items
            error_log("Error al insertar item de pedido: " . mysqli_error(conectarDB()));
        }
        
        // Actualizar stock si existe la columna
        $resultado_columna = query("SHOW COLUMNS FROM productos LIKE 'stock'");
        if ($resultado_columna->num_rows > 0) {
            query("UPDATE productos SET stock = stock - $cantidad WHERE id = $producto_id");
        }
    }
    
    // Responder con éxito
    echo json_encode([
        'success' => true,
        'pedido_id' => $pedido_id,
        'mensaje' => 'Pedido guardado correctamente'
    ]);
    
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    
    // Registrar error en log
    error_log("Error al procesar pedido: " . $e->getMessage());
}
?>