<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Verificar si hay un ID de preferencia
$preference_id = isset($_GET['preference_id']) ? $_GET['preference_id'] : null;
$payment_method = isset($_GET['payment_method']) ? $_GET['payment_method'] : 'manual';

// Obtener información del pago desde la base de datos
$payment_info = null;
if ($preference_id) {
    $query = "SELECT * FROM pagos WHERE preference_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $preference_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $payment_info = $result->fetch_assoc();
    }
}

// Incluir el encabezado
include 'header.php';
?>

<section class="success-section">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="success-title">¡Pedido Recibido!</h2>
            <p>Tu pedido ha sido registrado correctamente. A continuación, te proporcionamos los detalles para completar el pago.</p>
            
            <div class="payment-details">
                <p><strong>ID de pedido:</strong> <?php echo htmlspecialchars($preference_id); ?></p>
                <p><strong>Estado:</strong> Pendiente de pago</p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
                
                <?php if ($payment_method === 'efectivo'): ?>
                <div class="payment-instructions">
                    <h3>Instrucciones para pago en efectivo</h3>
                    <p>Para completar tu compra, puedes realizar el pago en efectivo en nuestra tienda física:</p>
                    <p><strong>Dirección:</strong> Av. Ejemplo 1234, Parque Chacabuco, CABA</p>
                    <p><strong>Horario:</strong> Lunes a Viernes de 9:00 a 18:00</p>
                    <p><strong>Descuento aplicado:</strong> 10% sobre el total</p>
                    <p>Menciona tu número de pedido al momento de pagar.</p>
                </div>
                <?php elseif ($payment_method === 'transferencia'): ?>
                <div class="payment-instructions">
                    <h3>Instrucciones para transferencia bancaria</h3>
                    <p>Para completar tu compra, realiza una transferencia bancaria a la siguiente cuenta:</p>
                    <p><strong>Banco:</strong> Banco Ejemplo</p>
                    <p><strong>Titular:</strong> Bear Shop S.A.</p>
                    <p><strong>CBU:</strong> 0000000000000000000000</p>
                    <p><strong>Alias:</strong> BEAR.SHOP.VENTAS</p>
                    <p><strong>CUIT:</strong> 30-12345678-9</p>
                    <p>Una vez realizada la transferencia, envía el comprobante a <strong>pagos@bearshop.com.ar</strong> indicando tu número de pedido.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn">Volver al inicio</a>
            </div>
        </div>
    </div>
</section>

<script>
// Limpiar el carrito después de un pago exitoso
localStorage.removeItem('bearShopCart');
</script>

<style>
.success-section {
    padding: 80px 0;
    text-align: center;
}

.success-container {
    max-width: 600px;
    margin: 0 auto;
    background-color: #f8fff8;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 40px;
}

.success-icon {
    font-size: 4rem;
    color: #2ecc71;
    margin-bottom: 20px;
}

.success-title {
    color: #2ecc71;
    margin-bottom: 20px;
}

.payment-details {
    background-color: #f5f5f5;
    border-radius: 8px;
    padding: 15px;
    margin: 20px 0;
    text-align: left;
}

.payment-details p {
    margin: 5px 0;
}

.payment-instructions {
    background-color: #fff8e1;
    border-radius: 8px;
    padding: 15px;
    margin: 15px 0;
    text-align: left;
    border-left: 4px solid #f39c12;
}

.payment-instructions h3 {
    color: #f39c12;
    margin-top: 0;
    margin-bottom: 10px;
}

.payment-instructions p {
    margin: 8px 0;
}

.action-buttons {
    margin-top: 30px;
}

.action-buttons .btn {
    margin: 0 10px;
}
</style>

<?php
// Incluir el pie de página
include 'footer.php';
?>
