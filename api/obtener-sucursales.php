<?php
// Incluir archivos de configuración
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/correo-argentino.php';

// Verificar que sea una solicitud GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => true, 'message' => 'Método no permitido']);
    exit;
}

// Obtener código de provincia
$provinceCode = isset($_GET['province']) ? $_GET['province'] : null;

if (!$provinceCode) {
    http_response_code(400);
    echo json_encode(['error' => true, 'message' => 'Código de provincia requerido']);
    exit;
}

// Obtener sucursales
$result = obtenerSucursales($provinceCode);

if (!$result['success']) {
    http_response_code(500);
    echo json_encode(['error' => true, 'message' => $result['message']]);
    exit;
}

// Devolver sucursales
echo json_encode([
    'success' => true,
    'agencies' => $result['agencies']
]);