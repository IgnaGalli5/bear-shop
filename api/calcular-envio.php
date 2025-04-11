<?php
// Incluir archivos de configuración
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/correo-argentino.php';

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del carrito y dirección
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['cart']) || !is_array($data['cart']) || empty($data['cart'])) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Datos del carrito inválidos']);
    exit;
}

// Calcular subtotal del carrito
$subtotal = 0;
foreach ($data['cart'] as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

// Verificar que tengamos los datos mínimos necesarios
$shippingAddress = $data['shippingAddress'] ?? [];
if (!isset($shippingAddress['zipCode']) || empty($shippingAddress['zipCode'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'freeShipping' => false,
        'shippingCost' => 2500, // Costo por defecto en caso de error
        'message' => 'Código postal de destino requerido',
        'origin' => SENDER_CITY . ' (' . SENDER_ZIP_CODE . ')'
    ]);
    exit;
}

// Calcular costo de envío usando la API de Correo Argentino
$shippingInfo = calcularCostoEnvio($data['cart'], $shippingAddress);

if (!$shippingInfo['success']) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'freeShipping' => false,
        'shippingCost' => 2500, // Costo por defecto en caso de error
        'message' => $shippingInfo['message'],
        'origin' => SENDER_CITY . ' (' . SENDER_ZIP_CODE . ')'
    ]);
    exit;
}

// Verificar si califica para envío gratis
$freeShipping = calificaParaEnvioGratis($subtotal);
$shippingCost = $freeShipping ? 0 : $shippingInfo['cost'];

// Devolver la información del envío
echo json_encode([
    'success' => true,
    'freeShipping' => $freeShipping,
    'shippingCost' => $shippingCost,
    'originalShippingCost' => $shippingInfo['cost'], // Costo original sin bonificación
    'deliveryType' => $shippingInfo['deliveryType'],
    'productType' => $shippingInfo['productType'],
    'productName' => $shippingInfo['productName'],
    'deliveryTimeMin' => $shippingInfo['deliveryTimeMin'],
    'deliveryTimeMax' => $shippingInfo['deliveryTimeMax'],
    'weight' => $shippingInfo['weight'],
    'declaredValue' => $shippingInfo['declaredValue'],
    'message' => $freeShipping ? 'Tu pedido califica para envío gratis' : 'Costo de envío calculado correctamente',
    'origin' => SENDER_CITY . ' (' . SENDER_ZIP_CODE . ')'
]);
