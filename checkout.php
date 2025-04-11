<?php
// Configuración básica
$site_name = 'Bear Shop';
$primary_color = '#945a42';

// Intentar incluir archivos de configuración
if (file_exists('includes/config.php')) {
    require_once 'includes/config.php';
} else {
    // Valores por defecto si no existe el archivo
    define('SITE_NAME', $site_name);
    define('MERCADOPAGO_PUBLIC_KEY', 'TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx');
    define('MERCADOPAGO_ENV', 'test');
}

// Intentar conectar a la base de datos
if (file_exists('includes/db.php')) {
    require_once 'includes/db.php';
}

// Formatear precio
function formatPrice($price) {
    return number_format($price, 0, ',', '.');
}

// Generar HTML del encabezado
$header_html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - ' . $site_name . '</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.cdnfonts.com/css/bogart" rel="stylesheet">
    <link rel="shortcut icon" href="img/icon.png" />
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="logo">
                <h1>BEAR</h1>
            </div>
            <nav>
                <ul class="menu">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="index.php#productos">Productos</a></li>
                    <li><a href="index.php#carrito">Carrito</a></li>
                </ul>
            </nav>
        </div>
    </header>';

echo $header_html;
?>

<section class="checkout-section">
    <div class="container">
        <div class="checkout-container">
            <h2 class="section-title">Finalizar Compra</h2>
            
            <?php if (defined('MERCADOPAGO_ENV') && MERCADOPAGO_ENV === 'test'): ?>
            <div class="test-mode-alert">
                <div class="alert-icon"><i class="fas fa-info-circle"></i></div>
                <div class="alert-content">
                    <h3>Modo de prueba activo</h3>
                    <p>Usa estas tarjetas para probar:</p>
                    <ul>
                        <li><strong>Tarjeta VISA:</strong> 4509 9535 6623 3704</li>
                        <li><strong>Código de seguridad:</strong> 123</li>
                        <li><strong>Fecha de vencimiento:</strong> cualquiera futura</li>
                        <li><strong>Nombre:</strong> APRO (aprobado) o OTHE (rechazado)</li>
                    </ul>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="checkout-content" id="checkout-content">
                <div class="checkout-summary">
                    <h3>Resumen de compra</h3>
                    
                    <div class="checkout-items" id="checkout-items">
                        <!-- Los items se cargarán dinámicamente con JavaScript -->
                    </div>
                    
                    <div class="checkout-total">
                        <span>Total:</span>
                        <span class="total-price" id="checkout-total">$0</span>
                    </div>
                </div>
                
                <div class="payment-section">
                    <h3>Método de pago</h3>
                    <div class="payment-options">
                        <?php 
                        // Obtener el método de pago seleccionado (si existe)
                        $payment_method = isset($_GET['payment']) ? $_GET['payment'] : 'mercadopago';
                        
                        // Mostrar la opción correspondiente
                        if ($payment_method === 'mercadopago'): 
                        ?>
                        <div class="payment-option">
                            <img src="img/mercadopago-logo.png" alt="MercadoPago" onerror="this.src='https://www.mercadopago.com/org-img/MP3/API/logos/mp-logo.png'; this.onerror='';">
                            <p>Paga de manera segura con MercadoPago. Aceptamos tarjetas de crédito, débito y otros métodos de pago.</p>
                        </div>
                        <?php elseif ($payment_method === 'efectivo'): ?>
                        <div class="payment-option">
                            <i class="fas fa-money-bill-wave"></i>
                            <p>Has seleccionado pago en efectivo. Recibirás instrucciones para completar tu pago después de finalizar tu pedido.</p>
                            <p class="discount-note">Se aplicará un 10% de descuento sobre el total de tu compra.</p>
                        </div>
                        <?php elseif ($payment_method === 'transferencia'): ?>
                        <div class="payment-option">
                            <i class="fas fa-university"></i>
                            <p>Has seleccionado transferencia bancaria. Recibirás los datos bancarios para realizar la transferencia después de finalizar tu pedido.</p>
                        </div>
                        <?php else: ?>
                        <div class="payment-option">
                            <img src="img/mercadopago-logo.png" alt="MercadoPago" onerror="this.src='https://www.mercadopago.com/org-img/MP3/API/logos/mp-logo.png'; this.onerror='';">
                            <p>Paga de manera segura con MercadoPago. Aceptamos tarjetas de crédito, débito y otros métodos de pago.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="checkout-button-container">
                        <div class="loading" id="payment-loading">
                            <div class="loading-spinner"></div>
                            <p>Preparando el pago...</p>
                        </div>
                        <div id="checkout-button"></div>
                    </div>
                </div>
            </div>
            
            <div class="empty-cart-message" id="empty-cart-message" style="display: none;">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h3>Tu carrito está vacío</h3>
                <p>Parece que aún no has agregado productos a tu carrito.</p>
                <a href="index.php#productos" class="btn">Ver productos</a>
            </div>
        </div>
    </div>
</section>

<!-- Script de MercadoPago.js -->
<script src="https://sdk.mercadopago.com/js/v2"></script>
<script>
// Función para formatear precio
function formatPrice(price) {
    return price.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}

// Función para obtener el carrito
function getCart() {
    try {
        const cartData = localStorage.getItem('bearShopCart');
        return cartData ? JSON.parse(cartData) : [];
    } catch (error) {
        console.error('Error al obtener el carrito:', error);
        return [];
    }
}

// Función para mostrar el resumen del carrito
function showCartSummary(cart) {
    const checkoutContent = document.getElementById('checkout-content');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const checkoutItems = document.getElementById('checkout-items');
    const checkoutTotal = document.getElementById('checkout-total');
    
    if (!cart || cart.length === 0) {
        checkoutContent.style.display = 'none';
        emptyCartMessage.style.display = 'block';
        return;
    }
    
    checkoutContent.style.display = 'block';
    emptyCartMessage.style.display = 'none';
    
    // Limpiar contenedor de items
    checkoutItems.innerHTML = '';
    
    // Calcular subtotal y generar HTML para cada item
    let subtotal = 0;
    cart.forEach(item => {
        const itemTotal = item.price * item.quantity;
        subtotal += itemTotal;
        
        const itemElement = document.createElement('div');
        itemElement.className = 'checkout-item';
        itemElement.innerHTML = `
            <div class="item-details">
                <span class="item-name">${item.name} x ${item.quantity}</span>
                <span class="item-price">$${formatPrice(itemTotal)}</span>
            </div>
        `;
        
        checkoutItems.appendChild(itemElement);
    });
    
    // Obtener información de envío
    let shippingCost = 0;
    let freeShipping = false;
    
    try {
        const storedShippingCost = localStorage.getItem('bearShopShippingCost');
        if (storedShippingCost) {
            shippingCost = parseFloat(storedShippingCost);
        }
        
        freeShipping = localStorage.getItem('bearShopFreeShipping') === 'true';
    } catch (error) {
        console.error('Error al obtener costo de envío:', error);
    }
    
    // Mostrar costo de envío
    const shippingElement = document.createElement('div');
    shippingElement.className = 'checkout-item shipping';
    shippingElement.innerHTML = `
        <div class="item-details">
            <span class="item-name">Envío</span>
            <span class="item-price">${freeShipping ? '<span class="free-shipping">Gratis</span>' : '$' + formatPrice(shippingCost)}</span>
        </div>
    `;
    
    checkoutItems.appendChild(shippingElement);
    
    // Calcular y mostrar total
    const total = subtotal + (freeShipping ? 0 : shippingCost);
    checkoutTotal.textContent = '$' + formatPrice(total);
    
    // Crear preferencia de pago
    createPaymentPreference(cart);
}

// Función para crear la preferencia de pago
function createPaymentPreference(cart) {
    // Obtener información del cliente si está disponible
    const customerInfo = {};
   
    // Obtener método de pago seleccionado
    const paymentMethod = localStorage.getItem('bearShopPaymentMethod') || 'mercadopago';
   
    // Verificar si hay información de envío
    let shippingData = {};
    try {
        const shippingAddress = localStorage.getItem('bearShopShippingAddress');
        if (shippingAddress) {
            shippingData.shippingAddress = JSON.parse(shippingAddress);
        }
       
        const shippingCost = localStorage.getItem('bearShopShippingCost');
        if (shippingCost) {
            shippingData.shippingCost = parseFloat(shippingCost);
        } else {
            // Valor por defecto si no hay costo de envío guardado
            shippingData.shippingCost = 2500;
        }
       
        shippingData.freeShipping = localStorage.getItem('bearShopFreeShipping') === 'true';
        shippingData.shippingProductName = localStorage.getItem('bearShopShippingProductName') || 'Correo Argentino';
        shippingData.shippingDeliveryTime = localStorage.getItem('bearShopShippingDeliveryTime') || '';
    } catch (error) {
        console.error('Error al obtener datos de envío:', error);
        // Valores por defecto
        shippingData = {
            shippingCost: 2500,
            freeShipping: false,
            shippingProductName: 'Correo Argentino',
            shippingDeliveryTime: ''
        };
    }
   
    // Mostrar el spinner de carga
    document.getElementById('payment-loading').style.display = 'flex';
   
    fetch('api/create-preference.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            cart: cart,
            customer: customerInfo,
            paymentMethod: paymentMethod,
            // Incluir la información de envío
            shippingCost: shippingData.shippingCost,
            freeShipping: shippingData.freeShipping,
            shippingProductName: shippingData.shippingProductName,
            shippingDeliveryTime: shippingData.shippingDeliveryTime,
            shippingAddress: shippingData.shippingAddress
        })
    })
    .then(response => {
        // Verificar primero si la respuesta es JSON válido
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            // Si no es JSON, obtener el texto para mostrar el error
            return response.text().then(text => {
                throw new Error('Respuesta no válida del servidor: ' + text.substring(0, 100) + '...');
            });
        }
        
        if (!response.ok) {
            throw new Error('Error al crear la preferencia de pago');
        }
        
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.message || 'Error al crear la preferencia de pago');
        }
        
        // Ocultar el spinner de carga
        document.getElementById('payment-loading').style.display = 'none';
        
        // Si el método de pago no es MercadoPago, redirigir a la página correspondiente
        if (paymentMethod !== 'mercadopago') {
            window.location.href = data.redirectUrl || 'success.php?payment_id=manual&status=pending&preference_id=' + data.id;
            return;
        }
        
        // Inicializar MercadoPago
        const mp = new MercadoPago('<?php echo defined('MERCADOPAGO_PUBLIC_KEY') ? MERCADOPAGO_PUBLIC_KEY : 'TEST-xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx'; ?>', {
            locale: 'es-AR'
        });
        
        // Renderizar botón de pago
        mp.checkout({
            preference: {
                id: data.id
            },
            render: {
                container: '#checkout-button',
                label: 'Pagar ahora'
            },
            theme: {
                elementsColor: '<?php echo $primary_color; ?>',
                headerColor: '<?php echo $primary_color; ?>'
            }
        });
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('payment-loading').innerHTML = `
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <p>Error: ${error.message}</p>
                <button class="btn" onclick="window.location.reload()">Reintentar</button>
            </div>
        `;
    });
}

// Cargar el carrito y mostrar el resumen
document.addEventListener('DOMContentLoaded', function() {
    const cart = getCart();
    showCartSummary(cart);
    
    // Añadir animación de entrada
    setTimeout(() => {
        document.querySelector('.checkout-container').classList.add('show');
    }, 100);
});
</script>

<style>
:root {
    --color-primary: <?php echo $primary_color; ?>;
    --color-primary-light: #b27a62;
    --color-primary-dark: #7a4a35;
    --color-light: rgb(238, 200, 163);
    --color-success: #2ecc71;
    --color-warning: #f39c12;
    --color-white: #ffffff;
    --color-gray: #f5f5f5;
    --color-text: #333333;
    --border-radius: 8px;
    --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

/* Estilos generales */
body {
    font-family: 'Bogart', Arial, sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
    color: var(--color-text);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.btn {
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
    text-align: center;
}

.btn:hover {
    background-color: var(--color-primary-dark);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

/* Estilos del encabezado */
.site-header {
    background-color: var(--color-primary);
    color: white;
    padding: 15px 0;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.site-header .container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo h1 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: bold;
}

.menu {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
}

.menu li {
    margin-left: 20px;
}

.menu a {
    color: white;
    text-decoration: none;
    font-weight: 500;
    transition: var(--transition);
}

.menu a:hover {
    color: var(--color-light);
}

/* Estilos para la página de checkout */
.checkout-section {
    padding: 60px 0;
}

.checkout-container {
    max-width: 800px;
    margin: 0 auto;
    background-color: var(--color-white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    padding: 30px;
    transform: translateY(20px);
    opacity: 0;
    transition: var(--transition);
}

.checkout-container.show {
    transform: translateY(0);
    opacity: 1;
}

.section-title {
    text-align: center;
    margin-bottom: 30px;
    color: var(--color-primary);
    font-size: 2rem;
    position: relative;
    padding-bottom: 15px;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 3px;
    background-color: var(--color-primary);
    border-radius: 3px;
}

.test-mode-alert {
    background-color: #fff8e1;
    border-radius: var(--border-radius);
    padding: 20px;
    margin-bottom: 30px;
    display: flex;
    align-items: flex-start;
    border-left: 4px solid var(--color-warning);
}

.alert-icon {
    font-size: 2rem;
    color: var(--color-warning);
    margin-right: 15px;
    flex-shrink: 0;
}

.alert-content {
    flex-grow: 1;
}

.alert-content h3 {
    margin-top: 0;
    margin-bottom: 10px;
    color: var(--color-warning);
}

.alert-content p {
    margin-top: 0;
    margin-bottom: 10px;
}

.alert-content ul {
    margin: 0;
    padding-left: 20px;
}

.checkout-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.checkout-summary, .payment-section {
    background-color: #f9f9f9;
    border-radius: var(--border-radius);
    padding: 25px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.checkout-summary h3, .payment-section h3 {
    margin-top: 0;
    margin-bottom: 20px;
    color: var(--color-primary);
    font-size: 1.4rem;
    border-bottom: 2px solid var(--color-light);
    padding-bottom: 10px;
}

.checkout-items {
    margin-bottom: 20px;
}

.checkout-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.checkout-item:last-child {
    border-bottom: none;
}

.item-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.item-name {
    flex-grow: 1;
    padding-right: 15px;
}

.item-price {
    font-weight: bold;
    white-space: nowrap;
}

.shipping {
    background-color: rgba(238, 200, 163, 0.2);
    border-radius: var(--border-radius);
    padding: 15px !important;
    margin-top: 10px;
}

.free-shipping {
    color: var(--color-success);
    font-weight: bold;
}

.checkout-total {
    display: flex;
    justify-content: space-between;
    padding: 20px 0;
    margin-top: 15px;
    border-top: 2px solid var(--color-primary-light);
    font-size: 1.3em;
    font-weight: bold;
}

.total-price {
    color: var(--color-primary);
}

.payment-options {
    margin-bottom: 25px;
}

.payment-option {
    display: flex;
    align-items: center;
    background-color: white;
    border-radius: var(--border-radius);
    padding: 15px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
}

.payment-option img {
    max-height: 40px;
    margin-right: 15px;
}

.payment-option p {
    margin: 0;
    color: #666;
    font-size: 0.9rem;
}

.checkout-button-container {
    margin-top: 25px;
    text-align: center;
}

.loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.loading-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid var(--color-primary);
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin-bottom: 15px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.error-message {
    color: #e74c3c;
    text-align: center;
    padding: 15px;
    background-color: #fde8e8;
    border-radius: var(--border-radius);
    margin-top: 15px;
}

.error-message i {
    font-size: 2rem;
    margin-bottom: 10px;
    color: #e74c3c;
}

.error-message p {
    margin: 10px 0;
}

.empty-cart-message {
    text-align: center;
    padding: 40px 20px;
}

.empty-cart-icon {
    font-size: 4rem;
    color: #ccc;
    margin-bottom: 20px;
}

.empty-cart-message h3 {
    margin-top: 0;
    color: var(--color-primary);
}

.empty-cart-message p {
    margin-bottom: 25px;
    color: #777;
}

/* Estilos responsivos */
@media (max-width: 768px) {
    .checkout-section {
        padding: 40px 0;
    }
    
    .checkout-container {
        padding: 20px;
    }
    
    .section-title {
        font-size: 1.8rem;
    }
    
    .test-mode-alert {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .alert-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .checkout-content {
        gap: 20px;
    }
    
    .checkout-summary, .payment-section {
        padding: 20px;
    }
}

@media (max-width: 576px) {
    .checkout-section {
        padding: 30px 0;
    }
    
    .checkout-container {
        padding: 15px;
    }
    
    .section-title {
        font-size: 1.5rem;
    }
    
    .checkout-summary h3, .payment-section h3 {
        font-size: 1.2rem;
    }
    
    .checkout-total {
        font-size: 1.2em;
    }
    
    .payment-option {
        flex-direction: column;
        text-align: center;
    }
    
    .payment-option img {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>

<?php
// Generar HTML del pie de página
$footer_html = '<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-logo">
                <h2>BEAR</h2>
                <p>&copy; ' . date('Y') . ' ' . (defined('SITE_NAME') ? SITE_NAME : $site_name) . '. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</footer>
<style>
.site-footer {
    background-color: #333;
    color: white;
    padding: 30px 0;
    margin-top: 60px;
}

.footer-content {
    text-align: center;
}

.footer-logo h2 {
    margin-top: 0;
    margin-bottom: 10px;
    font-size: 1.8rem;
}

.footer-logo p {
    margin: 0;
    font-size: 0.9rem;
    opacity: 0.8;
}
</style>
</body>
</html>';

echo $footer_html;
?>