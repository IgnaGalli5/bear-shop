<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/newsletter.php';

// Establecer cabeceras para JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exito' => false, 'mensaje' => 'Método no permitido']);
    exit;
}

// Obtener datos
$email = isset($_POST['email']) ? $_POST['email'] : '';
$nombre = isset($_POST['nombre']) ? $_POST['nombre'] : '';

// Procesar suscripción
$resultado = procesarSuscripcion($email, $nombre);

// Devolver respuesta
echo json_encode($resultado);