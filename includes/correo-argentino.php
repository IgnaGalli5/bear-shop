<?php
// Configuración de Correo Argentino
define('CORREO_API_URL', 'https://api.correoargentino.com.ar/');
define('CORREO_API_KEY', 'TU_API_KEY'); // Reemplazar con tu API key real

// Información del remitente (origen)
define('SENDER_ZIP_CODE', '1406'); // Código postal de Parque Chacabuco
define('SENDER_CITY', 'Parque Chacabuco');
define('SENDER_STATE', 'C'); // C = Ciudad Autónoma de Buenos Aires
define('SENDER_STREET', 'Asamblea'); // Calle de la sucursal
define('SENDER_STREET_NUMBER', '1000'); // Número de la calle

// Umbral para envío gratis
define('FREE_SHIPPING_THRESHOLD', 60000); // $60.000 para envío gratis

/**
 * Verifica si un pedido califica para envío gratis
 * 
 * @param float $subtotal Subtotal del pedido
 * @return bool True si califica para envío gratis
 */
function calificaParaEnvioGratis($subtotal) {
    return $subtotal >= FREE_SHIPPING_THRESHOLD;
}

/**
 * Obtiene las sucursales de Correo Argentino por provincia
 * 
 * @param string $provinceCode Código de provincia
 * @return array Resultado con las sucursales
 */
function obtenerSucursales($provinceCode) {
    try {
        // Aquí deberías implementar la llamada real a la API de Correo Argentino
        // Este es un ejemplo simulado
        
        // Simular una demora en la respuesta
        usleep(500000); // 0.5 segundos
        
        // Datos de ejemplo para CABA (C)
        if ($provinceCode === 'C') {
            return [
                'success' => true,
                'agencies' => [
                    [
                        'code' => 'C000001',
                        'name' => 'Sucursal Parque Chacabuco',
                        'location' => [
                            'address' => [
                                'streetName' => 'Asamblea',
                                'streetNumber' => '1000',
                                'zipCode' => '1406',
                                'cityName' => 'CABA',
                                'stateName' => 'Ciudad Autónoma de Buenos Aires'
                            ]
                        ]
                    ],
                    [
                        'code' => 'C000002',
                        'name' => 'Sucursal Caballito',
                        'location' => [
                            'address' => [
                                'streetName' => 'Av. Rivadavia',
                                'streetNumber' => '5000',
                                'zipCode' => '1424',
                                'cityName' => 'CABA',
                                'stateName' => 'Ciudad Autónoma de Buenos Aires'
                            ]
                        ]
                    ],
                    [
                        'code' => 'C000003',
                        'name' => 'Sucursal Flores',
                        'location' => [
                            'address' => [
                                'streetName' => 'Av. Rivadavia',
                                'streetNumber' => '6800',
                                'zipCode' => '1406',
                                'cityName' => 'CABA',
                                'stateName' => 'Ciudad Autónoma de Buenos Aires'
                            ]
                        ]
                    ]
                ]
            ];
        }
        
        // Para otras provincias, devolver un array vacío o implementar según necesidad
        return [
            'success' => true,
            'agencies' => []
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al obtener sucursales: ' . $e->getMessage()
        ];
    }
}

/**
 * Calcula el costo de envío para un pedido
 * 
 * @param array $cart Carrito de compras
 * @param array $shippingAddress Dirección de envío
 * @return array Resultado con el costo de envío
 */
function calcularCostoEnvio($cart, $shippingAddress) {
    try {
        // Aquí deberías implementar la llamada real a la API de Correo Argentino
        // Este es un ejemplo simulado
        
        // Simular una demora en la respuesta
        usleep(1000000); // 1 segundo
        
        // Calcular peso total (ejemplo: 0.5kg por producto)
        $weight = 0;
        $declaredValue = 0;
        
        foreach ($cart as $item) {
            $weight += 0.5 * $item['quantity']; // 0.5kg por unidad
            $declaredValue += $item['price'] * $item['quantity'];
        }
        
        // Asegurar peso mínimo
        $weight = max(0.1, $weight);
        
        // Calcular costo basado en distancia (ejemplo simplificado)
        $destinationZip = $shippingAddress['zipCode'];
        $deliveryType = $shippingAddress['deliveryType'] ?? 'D'; // D = Domicilio, S = Sucursal
        
        // Costo base según tipo de entrega
        $baseCost = ($deliveryType === 'D') ? 2500 : 1800;
        
        // Ajustar según código postal (ejemplo)
        $firstDigit = substr($destinationZip, 0, 1);
        $costMultiplier = 1.0;
        
        // Códigos postales más lejanos tienen mayor costo
        switch ($firstDigit) {
            case '1': // CABA y alrededores
                $costMultiplier = 1.0;
                $deliveryTimeMin = 1;
                $deliveryTimeMax = 3;
                break;
            case '2': // Buenos Aires cercana
            case '3':
                $costMultiplier = 1.2;
                $deliveryTimeMin = 2;
                $deliveryTimeMax = 4;
                break;
            case '4': // Provincias cercanas
            case '5':
                $costMultiplier = 1.5;
                $deliveryTimeMin = 3;
                $deliveryTimeMax = 5;
                break;
            default: // Provincias lejanas
                $costMultiplier = 1.8;
                $deliveryTimeMin = 4;
                $deliveryTimeMax = 7;
                break;
        }
        
        // Calcular costo final
        $shippingCost = round($baseCost * $costMultiplier);
        
        // Determinar tipo de producto
        $productType = ($deliveryType === 'D') ? 'PaP' : 'PaS'; // Puerta a Puerta o Puerta a Sucursal
        $productName = ($deliveryType === 'D') ? 'Entrega a Domicilio' : 'Retiro en Sucursal';
        
        return [
            'success' => true,
            'cost' => $shippingCost,
            'weight' => $weight,
            'declaredValue' => $declaredValue,
            'deliveryType' => $deliveryType,
            'productType' => $productType,
            'productName' => $productName,
            'deliveryTimeMin' => $deliveryTimeMin,
            'deliveryTimeMax' => $deliveryTimeMax,
            'message' => 'Costo de envío calculado correctamente'
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => 'Error al calcular envío: ' . $e->getMessage()
        ];
    }
}