<?php
// Incluir archivos de configuración
require_once 'includes/config.php';
require_once 'includes/db.php';

// Verificar si hay información del pago fallido
$payment_id = isset($_GET['payment_id']) ? $_GET['payment_id'] : null;
$status = isset($_GET['status']) ? $_GET['status'] : 'rejected';
$external_reference = isset($_GET['external_reference']) ? $_GET['external_reference'] : null;
$preference_id = isset($_GET['preference_id']) ? $_GET['preference_id'] : null;

// Actualizar la información del pago en la base de datos
if ($preference_id) {
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
        <title>Pago Rechazado - Bear Shop</title>
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

<section class="failure-section">
    <div class="container">
        <div class="failure-container">
            <div class="failure-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <h2 class="failure-title">Error en el Pago</h2>
            <p>Ha ocurrido un error al procesar tu pago. Por favor, intenta nuevamente.</p>
            
            <div class="action-buttons">
                <a href="checkout.php" class="btn">Reintentar pago</a>
                <a href="index.php#carrito" class="btn">Volver al carrito</a>
            </div>
        </div>
    </div>
</section>

<style>
.failure-section {
    padding: 80px 0;
    text-align: center;
}

.failure-container {
    max-width: 600px;
    margin: 0 auto;
    background-color: #fff8f8;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    padding: 40px;
}

.failure-icon {
    font-size: 4rem;
    color: #e74c3c;
    margin-bottom: 20px;
}

.failure-title {
    color: #e74c3c;
    margin-bottom: 20px;
}

.action-buttons {
    margin-top: 30px;
}

.action-buttons .btn {
    margin: 0 10px;
    display: inline-block;
    padding: 10px 20px;
    background-color: #945a42;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    border: none;
    cursor: pointer;
    transition: background-color 0.3s;
}

.action-buttons .btn:hover {
    background-color: #7a4a35;
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