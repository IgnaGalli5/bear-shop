<?php
require_once 'config.php';
require_once 'db.php';

// Procesar suscripción
function procesarSuscripcion($email, $nombre = '') {
    $email = escapar($email);
    $nombre = escapar($nombre);
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return [
            'exito' => false,
            'mensaje' => 'Email inválido'
        ];
    }
    
    // Verificar si ya está suscrito
    $resultado = query("SELECT id FROM suscriptores WHERE email = '$email' LIMIT 1");
    if ($resultado->num_rows > 0) {
        return [
            'exito' => false,
            'mensaje' => 'Este email ya está suscrito'
        ];
    }
    
    // Insertar en la base de datos
    $sql = "INSERT INTO suscriptores (email, nombre) VALUES ('$email', '$nombre')";
    if (query($sql)) {
        return [
            'exito' => true,
            'mensaje' => '¡Gracias por suscribirte!'
        ];
    } else {
        return [
            'exito' => false,
            'mensaje' => 'Error al procesar la suscripción'
        ];
    }
}