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