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

try {
    // Inicializar cURL para obtener información del pago
    $ch = curl_init();

    // Configurar opciones de cURL
    curl_setopt($ch, CURLOPT_URL, "https://api.mercadopago.com/v1/payments/$payment_id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . MERCADOPAGO_ACCESS_TOKEN
    ]);

    // Ejecutar la solicitud
    $response = curl_exec($ch);

    // Verificar si hubo errores
    if (curl_errno($ch)) {
        curl_close($ch);
        throw new Exception('Error de cURL: ' . curl_error($ch));
    }

    // Cerrar la conexión cURL
    curl_close($ch);

    // Decodificar la respuesta
    $payment = json_decode($response, true);

    // Verificar si se obtuvo la información correctamente
    if (!isset($payment['status'])) {
        http_response_code(404);
        exit;
    }

    // Obtener datos del pago
    $status = $payment['status'];
    $external_reference = $payment['external_reference'] ?? '';
    $preference_id = $payment['preference_id'] ?? '';
    $payer_email = isset($payment['payer']) && isset($payment['payer']['email']) ? $payment['payer']['email'] : '';
    $amount = $payment['transaction_amount'] ?? 0;

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
        // Buscar por preference_id
        $query = "SELECT * FROM pagos WHERE preference_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $preference_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Actualizar registro existente por preference_id
            $query = "UPDATE pagos SET payment_id = ?, status = ?, payer_email = ?, fecha = NOW() WHERE preference_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssss", $payment_id, $status, $payer_email, $preference_id);
        } else {
            // Crear nuevo registro
            $query = "INSERT INTO pagos (payment_id, preference_id, external_reference, status, amount, payer_email, fecha) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssssds", $payment_id, $preference_id, $external_reference, $status, $amount, $payer_email);
        }
    }

    $stmt->execute();

    // Responder con éxito
    http_response_code(200);
    echo "OK";

} catch (Exception $e) {
    // Registrar el error
    error_log('Error en IPN: ' . $e->getMessage());
    http_response_code(500);
}
?>
