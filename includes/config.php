

<?php
// Configuración general del sitio
define('SITE_NAME', 'Bear.Shop');
define('SITE_URL', 'http://localhost/bear_shop'); // Actualiza esto a tu URL real

/// Configuración de MercadoPago
define('MERCADOPAGO_PUBLIC_KEY', 'APP_USR-53b4213a-8ab6-4725-9b77-edb54c8d36c9'); // Reemplaza con tu clave pública
define('MERCADOPAGO_ACCESS_TOKEN', 'APP_USR-1066541917730952-041013-f1bc67c6c8b6ed53791c55e662bf4450-2384869526'); // Reemplaza con tu token de acceso

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bear_shop');

// Otras configuraciones
define('CASH_DISCOUNT_PERCENT', 10); // Porcentaje de descuento para pagos en efectivo

// Configuración de entorno (producción o prueba)
define('MERCADOPAGO_ENV', 'test'); // Cambiar a 'prod' para producción
?>


