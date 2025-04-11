<?php
// Incluir archivos de configuración
require_once '../includes/config.php';
require_once '../includes/db.php';

// Obtener el ID del pago desde la notificación
$payment_id = isset($_GET['id']) ? $_GET['id'] : null;
$topic = isset($_GET['topic']) ? $_GET['topic'] : null;

// Verificar que sea una notificación válida
if (!$payment_id || $topic !== 'payment') {
    http_response_code(400);
    exit;
}

// Obtener información del pago desde MercadoPago
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . MERCADOPAGO_ACCESS_TOKEN
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    curl_close($ch);
    http_response_code(500);
    exit;
}

curl_close($ch);
$payment_info = json_decode($response, true);

// Verificar si se obtuvo la información correctamente
if (!isset($payment_info['status'])) {
    http_response_code(400);
    exit;
}

// Obtener datos del pago
$status = $payment_info['status'];
$external_reference = $payment_info['external_reference'] ?? '';
$preference_id = $payment_info['preference_id'] ?? '';
$payer_email = $payment_info['payer']['email'] ?? '';
$amount = $payment_info['transaction_amount'] ?? 0;

// Actualizar la información en la base de datos
$query = "SELECT * FROM pagos WHERE payment_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $payment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Actualizar registro existente
    $query = "UPDATE pagos SET status = ?, payer_email = ?, fecha = NOW() WHERE payment_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("sss", $status, $payer_email, $payment_id);
} else {
    // Crear nuevo registro
    $query = "INSERT INTO pagos (payment_id, preference_id, external_reference, status, amount, payer_email, fecha) 
              VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssds", $payment_id, $preference_id, $external_reference, $status, $amount, $payer_email);
}

$stmt->execute();

// Responder con éxito
http_response_code(200);
echo "OK";
?>