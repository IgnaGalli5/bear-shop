<?php
// Incluir archivos de configuración
require_once 'includes/config.php';
require_once 'includes/db.php';

// Verificar si hay un pago aprobado
$payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : null;
$external_reference = isset($_GET['external_reference']) ? $_GET['external_reference'] : null;
$preference_id = isset($_GET['preference_id']) ? $_GET['preference_id'] : null;
$payment_type = isset($_GET['payment_type']) ? $_GET['payment_type'] : null;
$payment_method_id = isset($_GET['payment_method_id']) ? $_GET['payment_method_id'] : null;

// Actualizar la información del pago en la base de datos
if ($payment_id && $status === 'approved' && $preference_id) {
    // Usar las funciones de db.php para conectar a la base de datos
    $conexion = conectarDB();
    $query = "UPDATE pagos SET payment_id = ?, status = ?, fecha = NOW() WHERE preference_id = ?";
    $stmt = $conexion->prepare($query);
    $stmt->bind_param("sss", $payment_id, $status, $preference_id);
    $stmt->execute();
    $stmt->close();
    $conexion->close();
}

// Intentar incluir el encabezado si existe
if (file_exists('header.php')) {
    include 'header.php';
} else {
    // Encabezado básico si no existe el archivo
    echo '<!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pago Exitoso - Bear Shop</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 0;
                background-color: #f9f9f9;
                color: #333;
            }
            header {
                background-color: #945a42;
                color: white;
                padding: 20px;
                text-align: center;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }
        </style>
    </head>
    <body>
        <header>
            <div class="container">
                <h1>BEAR SHOP</h1>
            </div>
        </header>';
}
?>

<section class="success-section">
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2 class="success-title">¡Pago Exitoso!</h2>
            <p>Tu pago ha sido procesado correctamente. Gracias por tu compra.</p>
            
            <div class="payment-details">
                <p><strong>ID de pago:</strong> <?php echo htmlspecialchars($payment_id); ?></p>
                <p><strong>Estado:</strong> Aprobado</p>
                <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?></p>
            </div>
            
            <!-- Botón para abrir el modal de WhatsApp -->
            <div class="whatsapp-button">
                <button id="open-whatsapp-modal" class="btn">Enviar datos por WhatsApp</button>
            </div>
            
            <div class="action-buttons">
                <a href="index.php" class="btn">Volver al inicio</a>
            </div>
        </div>
    </div>
</section>

<!-- Modal para enviar datos por WhatsApp -->
<div id="whatsapp-modal" class="modal">
    <div class="modal-content">
        <span class="close" id="close-whatsapp-modal">&times;</span>
        <div class="modal-header">
            <div class="modal-icon">
                <i class="fab fa-whatsapp"></i>
            </div>
            <h2>Completa tus datos para finalizar el pedido</h2>
            <p>Por favor completa la siguiente información para que podamos procesar tu pedido correctamente.</p>
        </div>
        
        <form id="whatsapp-form">
            <div class="form-group">
                <label for="customer-name">Nombre completo</label>
                <input type="text" id="customer-name" required>
            </div>
            
            <div class="form-group">
                <label for="customer-email">Email</label>
                <input type="email" id="customer-email" required>
            </div>
            
            <div class="form-group">
                <label for="customer-phone">Teléfono</label>
                <input type="tel" id="customer-phone" required>
            </div>
            
            <div class="form-group">
                <label for="customer-address">Dirección</label>
                <input type="text" id="customer-address" required>
            </div>
            
            <div class="form-group">
                <label for="customer-postal-code">Código Postal</label>
                <input type="text" id="customer-postal-code" required>
            </div>
            
            <div class="form-group">
                <label for="customer-details">Aclaraciones (color, tonalidad, etc.)</label>
                <textarea id="customer-details" rows="3" placeholder="Ingrese detalles del producto, por ejemplo, la tonalidad o color"></textarea>
            </div>
            
            <!-- Campos ocultos para la información del pago -->
            <input type="hidden" id="payment-id" value="<?php echo htmlspecialchars($payment_id); ?>">
            <input type="hidden" id="payment-date" value="<?php echo date('d/m/Y H:i:s'); ?>">
            <input type="hidden" id="payment-method" value="<?php echo htmlspecialchars($payment_method_id); ?>">
            
            <button type="submit" class="btn" id="send-whatsapp">Enviar pedido por WhatsApp</button>
        </form>
    </div>
</div>

<script>
// Limpiar el carrito después de un pago exitoso
localStorage.removeItem('bearShopCart');

// Obtener elementos del DOM
const openModalBtn = document.getElementById('open-whatsapp-modal');
const closeModalBtn = document.getElementById('close-whatsapp-modal');
const whatsappModal = document.getElementById('whatsapp-modal');
const whatsappForm = document.getElementById('whatsapp-form');

// Función para abrir el modal
openModalBtn.addEventListener('click', function() {
    whatsappModal.style.display = 'block';
    document.body.classList.add('modal-open');
    
    // Añadir animación de entrada
    setTimeout(() => {
        document.querySelector('.modal-content').classList.add('show');
    }, 10);
});

// Función para cerrar el modal
function closeModal() {
    document.querySelector('.modal-content').classList.remove('show');
    
    // Esperar a que termine la animación antes de ocultar el modal
    setTimeout(() => {
        whatsappModal.style.display = 'none';
        document.body.classList.remove('modal-open');
    }, 300);
}

closeModalBtn.addEventListener('click', closeModal);

// Cerrar el modal si se hace clic fuera de él
window.addEventListener('click', function(event) {
    if (event.target === whatsappModal) {
        closeModal();
    }
});

// Función para enviar datos por WhatsApp
whatsappForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Obtener datos del formulario
    const customerName = document.getElementById('customer-name').value.trim();
    const customerEmail = document.getElementById('customer-email').value.trim();
    const customerPhone = document.getElementById('customer-phone').value.trim();
    const customerAddress = document.getElementById('customer-address').value.trim();
    const customerPostalCode = document.getElementById('customer-postal-code').value.trim();
    const customerDetails = document.getElementById('customer-details').value.trim();
    
    // Obtener datos del pago
    const paymentId = document.getElementById('payment-id').value;
    const paymentDate = document.getElementById('payment-date').value;
    const paymentMethod = document.getElementById('payment-method').value;
    
    // Verificar campos obligatorios
    if (!customerName || !customerEmail || !customerPhone || !customerAddress || !customerPostalCode) {
        alert('Por favor complete todos los campos obligatorios');
        return;
    }
    
    // Verificar si el pago fue con "dinero en cuenta" para aplicar descuento
    const isAccountMoney = paymentMethod === 'account_money';
    const discountApplied = isAccountMoney ? '(10% de descuento aplicado)' : '';
    
    // Crear mensaje para WhatsApp
    let message = `*Nuevo Pedido con Pago Aprobado*\n\n`;
    message += `*Datos del Cliente:*\n`;
    message += `- Nombre: ${customerName}\n`;
    message += `- Email: ${customerEmail}\n`;
    message += `- Teléfono: ${customerPhone}\n`;
    message += `- Dirección: ${customerAddress}\n`;
    message += `- Código Postal: ${customerPostalCode}\n\n`;
    
    if (customerDetails) {
        message += `*Aclaraciones:*\n${customerDetails}\n\n`;
    }
    
    message += `*Datos del Pago:*\n`;
    message += `- ID de Pago: ${paymentId}\n`;
    message += `- Fecha: ${paymentDate}\n`;
    message += `- Estado: Aprobado\n`;
    message += `- Método: ${paymentMethod} ${discountApplied}\n\n`;
    
    message += `*Gracias por tu compra!*`;
    
    // Codificar mensaje para URL
    const encodedMessage = encodeURIComponent(message);
    
    // Número de WhatsApp (reemplazar con el número correcto)
    const whatsappNumber = "5491122834351"; // Reemplazar con el número real
    
    // Crear URL de WhatsApp
    const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`;
    
    // Mostrar mensaje de éxito
    const successMessage = document.createElement('div');
    successMessage.className = 'success-message';
    successMessage.innerHTML = '<i class="fas fa-check-circle"></i> ¡Datos enviados! Abriendo WhatsApp...';
    whatsappForm.appendChild(successMessage);
    
    // Esperar un momento antes de abrir WhatsApp
    setTimeout(() => {
        // Abrir WhatsApp en una nueva ventana
        window.open(whatsappURL, '_blank');
        
        // Cerrar el modal
        closeModal();
        
        // Eliminar el mensaje de éxito después de cerrar
        setTimeout(() => {
            if (whatsappForm.contains(successMessage)) {
                whatsappForm.removeChild(successMessage);
            }
        }, 300);
    }, 1000);
});
</script>

<style>
:root {
    --color-primary: #945a42;
    --color-primary-light: #b27a62;
    --color-primary-dark: #7a4a35;
    --color-light: rgb(238, 200, 163);
    --color-success: #2ecc71;
    --color-white: #ffffff;
    --color-gray: #f5f5f5;
    --color-text: #333333;
    --border-radius: 8px;
    --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

body.modal-open {
    overflow: hidden;
}

.success-section {
    padding: 80px 0;
    text-align: center;
}

.success-container {
    max-width: 600px;
    margin: 0 auto;
    background-color: #f8fff8;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 40px;
}

.success-icon {
    font-size: 4rem;
    color: var(--color-success);
    margin-bottom: 20px;
}

.success-title {
    color: var(--color-success);
    margin-bottom: 20px;
}

.payment-details {
    background-color: var(--color-gray);
    border-radius: var(--border-radius);
    padding: 15px;
    margin: 20px 0;
    text-align: left;
}

.payment-details p {
    margin: 5px 0;
}

.whatsapp-button {
    margin: 20px 0;
}

.action-buttons {
    margin-top: 30px;
}

.action-buttons .btn,
.whatsapp-button .btn {
    margin: 0 10px;
    display: inline-block;
    padding: 12px 25px;
    background-color: var(--color-primary);
    color: white;
    text-decoration: none;
    border-radius: var(--border-radius);
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: var(--transition);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.action-buttons .btn:hover,
.whatsapp-button .btn:hover {
    background-color: var(--color-primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Estilos para el modal */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(3px);
}

.modal-content {
    background: linear-gradient(to bottom, var(--color-light) 0%, #fff 100%);
    margin: 5% auto;
    padding: 0;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    max-width: 550px;
    width: 90%;
    position: relative;
    transform: translateY(-20px);
    opacity: 0;
    transition: all 0.3s ease-out;
    overflow: hidden;
}

.modal-content.show {
    transform: translateY(0);
    opacity: 1;
}

.modal-header {
    background-color: var(--color-primary);
    color: white;
    padding: 25px 30px;
    text-align: center;
    border-radius: var(--border-radius) var(--border-radius) 0 0;
}

.modal-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    color: white;
}

.modal-header h2 {
    margin: 0 0 10px 0;
    color: white;
}

.modal-header p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.95rem;
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 1.8rem;
    cursor: pointer;
    color: white;
    z-index: 10;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background-color: rgba(255, 255, 255, 0.2);
    transition: var(--transition);
}

.close:hover {
    background-color: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

#whatsapp-form {
    padding: 30px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: var(--color-primary-dark);
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: var(--border-radius);
    font-family: inherit;
    box-sizing: border-box;
    transition: var(--transition);
    background-color: rgba(255, 255, 255, 0.9);
}

.form-group input:focus,
.form-group textarea:focus {
    border-color: var(--color-primary);
    outline: none;
    box-shadow: 0 0 0 3px rgba(148, 90, 66, 0.2);
}

#send-whatsapp {
    width: 100%;
    padding: 14px;
    margin-top: 15px;
    background-color: #25D366; /* Color de WhatsApp */
    color: white;
    border: none;
    border-radius: var(--border-radius);
    font-weight: bold;
    font-size: 1.1rem;
    cursor: pointer;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);
}

#send-whatsapp:hover {
    background-color: #1da851;
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(37, 211, 102, 0.4);
}

#send-whatsapp:active {
    transform: translateY(0);
}

#send-whatsapp::before {
    content: '\f232';
    font-family: 'Font Awesome 5 Brands';
    margin-right: 10px;
    font-size: 1.2rem;
}

/* Mensaje de éxito */
.success-message {
    background-color: var(--color-success);
    color: white;
    padding: 15px;
    border-radius: var(--border-radius);
    margin-top: 20px;
    text-align: center;
    animation: fadeIn 0.3s ease-out;
}

.success-message i {
    margin-right: 10px;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .success-container {
        padding: 30px 20px;
    }
    
    .modal-content {
        margin: 10% auto;
    }
    
    .modal-header {
        padding: 20px;
    }
    
    #whatsapp-form {
        padding: 20px;
    }
}

@media (max-width: 576px) {
    .success-section {
        padding: 40px 0;
    }
    
    .success-container {
        padding: 25px 15px;
    }
    
    .success-icon {
        font-size: 3rem;
    }
    
    .success-title {
        font-size: 1.5rem;
    }
    
    .modal-content {
        margin: 15% auto 5% auto;
        width: 95%;
    }
    
    .modal-header {
        padding: 15px;
    }
    
    .modal-icon {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    
    .modal-header h2 {
        font-size: 1.4rem;
    }
    
    #whatsapp-form {
        padding: 15px;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        margin-bottom: 5px;
        font-size: 0.9rem;
    }
    
    .form-group input,
    .form-group textarea {
        padding: 10px;
    }
    
    #send-whatsapp {
        padding: 12px;
        font-size: 1rem;
    }
}
</style>

<?php
// Intentar incluir el pie de página si existe
if (file_exists('footer.php')) {
    include 'footer.php';
} else {
    // Pie de página básico si no existe el archivo
    echo '<footer style="background-color: #333; color: white; padding: 20px; text-align: center; margin-top: 40px;">
        <div class="container">
            <p>&copy; ' . date('Y') . ' Bear Shop. Todos los derechos reservados.</p>
        </div>
    </footer>
    </body>
    </html>';
}
?>