<?php
// Configuración inicial
header('Content-Type: application/json');

try {
    // Incluir archivos necesarios
    if (file_exists('../includes/config.php')) {
        require_once '../includes/config.php';
    } else {
        throw new Exception('El archivo de configuración no existe');
    }
    
    // Obtener datos de la solicitud
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception('Datos de entrada inválidos');
    }
    
    // Validar datos requeridos
    if (empty($data['cart'])) {
        throw new Exception('El carrito está vacío');
    }
    
    // Obtener método de pago
    $paymentMethod = isset($data['paymentMethod']) ? $data['paymentMethod'] : 'mercadopago';
    
    // Si no es MercadoPago, simplemente devolver una URL de redirección
    if ($paymentMethod !== 'mercadopago') {
        echo json_encode([
            'success' => true,
            'id' => 'manual-' . time(),
            'redirectUrl' => 'success.php?payment_id=manual&status=pending&payment_method=' . $paymentMethod
        ]);
        exit;
    }
    
    // Verificar que la constante esté definida
    if (!defined('MERCADOPAGO_ACCESS_TOKEN')) {
        throw new Exception('La constante MERCADOPAGO_ACCESS_TOKEN no está definida en config.php');
    }
    
    // Preparar los datos para la API de MercadoPago
    $preference_data = [
        'items' => [],
        'back_urls' => [
            'success' => SITE_URL . '/success.php',
            'failure' => SITE_URL . '/failure.php',
            'pending' => SITE_URL . '/pending.php'
        ],
        'auto_return' => 'approved',
        'binary_mode' => true
    ];
    
    // Crear ítems
    $total = 0;
    
    foreach ($data['cart'] as $item) {
        $preference_data['items'][] = [
            'title' => $item['name'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
            'currency_id' => 'ARS'
        ];
        
        $total += $item['price'] * $item['quantity'];
    }
    
    // Agregar envío como ítem si corresponde
    if (isset($data['shippingCost']) && $data['shippingCost'] > 0 && (!isset($data['freeShipping']) || !$data['freeShipping'])) {
        $preference_data['items'][] = [
            'title' => 'Envío - ' . ($data['shippingProductName'] ?? 'Correo Argentino'),
            'quantity' => 1,
            'unit_price' => $data['shippingCost'],
            'currency_id' => 'ARS'
        ];
        
        $total += $data['shippingCost'];
    }
    
    // Realizar la solicitud a la API de MercadoPago
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.mercadopago.com/checkout/preferences');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($preference_data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . MERCADOPAGO_ACCESS_TOKEN
    ]);
    
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    
    if (curl_errno($ch)) {
        throw new Exception('Error en la solicitud cURL: ' . curl_error($ch));
    }
    
    curl_close($ch);
    
    if ($status != 200 && $status != 201) {
        throw new Exception('Error en la API de MercadoPago: ' . $response);
    }
    
    $preference = json_decode($response, true);
    
    if (!$preference || !isset($preference['id'])) {
        throw new Exception('Respuesta inválida de MercadoPago: ' . $response);
    }
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'id' => $preference['id'],
        'init_point' => $preference['init_point'],
        'sandbox_init_point' => $preference['sandbox_init_point']
    ]);
    
} catch (Exception $e) {
    // Registrar el error para depuración
    error_log('Error en create-preference.php: ' . $e->getMessage());
    
    // Devolver respuesta de error en formato JSON
    echo json_encode([
        'success' => false,
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>