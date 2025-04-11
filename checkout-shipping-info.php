<?php
// Obtener información de envío de la sesión o localStorage
$shipping_cost = isset($_SESSION['shipping_cost']) ? $_SESSION['shipping_cost'] : 0;
$free_shipping = isset($_SESSION['free_shipping']) ? $_SESSION['free_shipping'] : false;
$shipping_origin = isset($_SESSION['shipping_origin']) ? $_SESSION['shipping_origin'] : 'Parque Chacabuco (1406)';
$shipping_product_name = isset($_SESSION['shipping_product_name']) ? $_SESSION['shipping_product_name'] : '';
$shipping_delivery_time = isset($_SESSION['shipping_delivery_time']) ? $_SESSION['shipping_delivery_time'] : '';

// Obtener dirección de envío
$shipping_address = isset($_SESSION['shipping_address']) ? $_SESSION['shipping_address'] : [];
$delivery_type = isset($shipping_address['deliveryType']) ? $shipping_address['deliveryType'] : 'D';
$delivery_type_text = $delivery_type === 'D' ? 'Entrega a domicilio' : 'Retiro en sucursal';

// Formatear dirección
$address_text = '';
if (!empty($shipping_address)) {
    if ($delivery_type === 'D') {
        $address_text = $shipping_address['streetName'] . ' ' . 
                        (isset($shipping_address['streetNumber']) ? $shipping_address['streetNumber'] : '') . ', ' . 
                        $shipping_address['cityName'] . ', ' . 
                        getProvinceNameByCode($shipping_address['state']) . ' (' . 
                        $shipping_address['zipCode'] . ')';
    } else {
        $address_text = 'Sucursal: ' . (isset($shipping_address['agencyName']) ? $shipping_address['agencyName'] : 'No especificada');
    }
}

// Función para obtener el nombre de la provincia por su código
function getProvinceNameByCode($code) {
    $provinces = [
        'C' => 'Ciudad Autónoma de Buenos Aires',
        'B' => 'Buenos Aires',
        'K' => 'Catamarca',
        'H' => 'Chaco',
        'U' => 'Chubut',
        'X' => 'Córdoba',
        'W' => 'Corrientes',
        'E' => 'Entre Ríos',
        'P' => 'Formosa',
        'Y' => 'Jujuy',
        'L' => 'La Pampa',
        'F' => 'La Rioja',
        'M' => 'Mendoza',
        'N' => 'Misiones',
        'Q' => 'Neuquén',
        'R' => 'Río Negro',
        'A' => 'Salta',
        'J' => 'San Juan',
        'D' => 'San Luis',
        'Z' => 'Santa Cruz',
        'S' => 'Santa Fe',
        'G' => 'Santiago del Estero',
        'V' => 'Tierra del Fuego',
        'T' => 'Tucumán'
    ];
    
    return isset($provinces[$code]) ? $provinces[$code] : $code;
}
?>

<!-- Información de envío en el checkout -->
<div class="checkout-section shipping-info">
    <h3>Información de envío</h3>
    
    <div class="checkout-item shipping-origin">
        <div class="item-details">
            <span class="item-name">Origen del envío</span>
            <span class="item-value"><?php echo htmlspecialchars($shipping_origin); ?></span>
        </div>
    </div>
    
    <div class="checkout-item shipping-address">
        <div class="item-details">
            <span class="item-name">Tipo de entrega</span>
            <span class="item-value"><?php echo htmlspecialchars($delivery_type_text); ?></span>
        </div>
    </div>
    
    <?php if (!empty($address_text)): ?>
    <div class="checkout-item shipping-address">
        <div class="item-details">
            <span class="item-name">Dirección de entrega</span>
            <span class="item-value"><?php echo htmlspecialchars($address_text); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($shipping_product_name)): ?>
    <div class="checkout-item shipping-service">
        <div class="item-details">
            <span class="item-name">Servicio</span>
            <span class="item-value"><?php echo htmlspecialchars($shipping_product_name); ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <?php if (!empty($shipping_delivery_time)): ?>
    <div class="checkout-item shipping-time">
        <div class="item-details">
            <span class="item-name">Tiempo estimado de entrega</span>
            <span class="item-value"><?php echo htmlspecialchars($shipping_delivery_time); ?> días hábiles</span>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="checkout-item shipping-cost">
        <div class="item-details">
            <span class="item-name">Costo de envío</span>
            <span class="item-value">
                <?php if ($free_shipping): ?>
                <span class="free-shipping">Gratis</span>
                <?php else: ?>
                $<?php echo number_format($shipping_cost, 0, ',', '.'); ?>
                <?php endif; ?>
            </span>
        </div>
    </div>
</div>
