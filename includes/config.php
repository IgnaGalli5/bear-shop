<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bear_shop');

// Configuración de la aplicación
define('SITE_URL', 'http://localhost/bear_shop');
define('ADMIN_URL', SITE_URL . '/admin');
define('API_URL', SITE_URL . '/api');

// Configuración de sesión
session_start();


//<?php
// Configuración de la base de datos para Hostinger
//define('DB_HOST', 'localhost'); // En la mayoría de los casos, sigue siendo 'localhost' en Hostinger
//define('DB_USER', 'u755296999_bear'); // Tu usuario en Hostinger
//define('DB_PASS', '$6SKlQpujZp'); // Tu contraseña en Hostinger
//define('DB_NAME', 'u755296999_bear'); // Nombre de la base de datos en Hostinger

// Configuración de la aplicación para producción
//define('SITE_URL', 'https://bearshop.com.ar'); // URL de tu sitio en producción
//define('ADMIN_URL', SITE_URL . '/admin');
//define('API_URL', SITE_URL . '/api');

// Configuración de sesión
//session_start();
//?