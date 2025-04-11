<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit;
}

// Obtener datos del envío
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Datos inválidos']);
    exit;
}

// Guardar datos en la sesión
$_SESSION['shipping_cost'] = $data['shipping_cost'] ?? 0;
$_SESSION['free_shipping'] = $data['free_shipping'] ?? false;
$_SESSION['shipping_origin'] = $data['shipping_origin'] ?? 'Parque Chacabuco (1406)';
$_SESSION['shipping_product_name'] = $data['shipping_product_name'] ?? '';
$_SESSION['shipping_delivery_time'] = $data['shipping_delivery_time'] ?? '';
$_SESSION['shipping_address'] = $data['shipping_address'] ?? [];

// Responder con éxito
echo json_encode(['success' => true]);
