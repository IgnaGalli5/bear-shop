<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Iniciar o continuar sesión
session_start();

// Verificar si hay un pedido para mostrar
if (!isset($_GET['id']) && !isset($_SESSION['ultimo_pedido'])) {
    header('Location: index.php');
    exit;
}

// Obtener ID del pedido
$pedido_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['ultimo_pedido'];

// Obtener datos del pedido
$pedido = null;
$items = [];

$resultado_pedido = query("SELECT * FROM pedidos WHERE id = $pedido_id LIMIT 1");
if ($resultado_pedido->num_rows > 0) {
    $pedido = $resultado_pedido->fetch_assoc();
    
    // Obtener items del pedido
    $resultado_items = query("
        SELECT pi.*, p.nombre, p.imagen 
        FROM pedido_items pi
        JOIN productos p ON pi.producto_id = p.id
        WHERE pi.pedido_id = $pedido_id
    ");
    
    while ($item = $resultado_items->fetch_assoc()) {
        $items[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #945a42;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            max-width: 800px;
            margin: 0 auto;
        }
        .logo h1 {
            margin: 0;
            font-size: 24px;
        }
        .confirmation-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            text-align: center;
        }
        .confirmation-icon {
            font-size: 64px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
        .confirmation-title {
            color: #333;
            font-size: 24px;
            margin-bottom: 15px;
        }
        .confirmation-message {
            color: #666;
            margin-bottom: 30px;
        }
        .order-details {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .order-details h2 {
            color: #945a42;
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .order-info {
            margin-bottom: 20px;
        }
        .order-info p {
            margin: 5px 0;
        }
        .order-info strong {
            color: #333;
        }
        .order-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .order-items th, .order-items td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .order-items th {
            background-color: #f9f9f9;
            font-weight: bold;
            color: #333;
        }
        .product-image {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .btn {
            background-color: #945a42;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #7a4a37;
        }
        .btn-outline {
            background-color: transparent;
            color: #945a42;
            border: 1px solid #945a42;
        }
        .btn-outline:hover {
            background-color: #f9f1e9;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>BEAR SHOP</h1>
            </div>
        </div>
    </header>
    
    <div class="container">
        <?php if ($pedido): ?>
            <div class="confirmation-card">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 class="confirmation-title">¡Pedido Confirmado!</h1>
                <p class="confirmation-message">
                    Gracias por tu compra. Tu pedido #<?php echo $pedido_id; ?> ha sido procesado correctamente.
                </p>
                <a href="index.php" class="btn">Continuar Comprando</a>
            </div>
            
            <div class="order-details">
                <h2>Detalles del Pedido</h2>
                
                <div class="order-info">
                    <p><strong>Número de Pedido:</strong> #<?php echo $pedido_id; ?></p>
                    <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha'])); ?></p>
                    <p><strong>Estado:</strong> <?php echo ucfirst($pedido['estado']); ?></p>
                    <p><strong>Método de Pago:</strong> <?php echo $pedido['metodo_pago']; ?></p>
                    <?php if (!empty($pedido['direccion_envio'])): ?>
                        <p><strong>Dirección de Envío:</strong> <?php echo $pedido['direccion_envio']; ?></p>
                    <?php endif; ?>
                </div>
                
                <h3>Productos</h3>
                <table class="order-items">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <?php if (!empty($item['imagen'])): ?>
                                            <img src="<?php echo $item['imagen']; ?>" alt="<?php echo $item['nombre']; ?>" class="product-image">
                                        <?php endif; ?>
                                        <span style="margin-left: 10px;"><?php echo $item['nombre']; ?></span>
                                    </div>
                                </td>
                                <td><?php echo $item['cantidad']; ?></td>
                                <td>$<?php echo number_format($item['precio_unitario'], 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($item['subtotal'], 2, ',', '.'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align: right;"><strong>Total:</strong></td>
                            <td><strong>$<?php echo number_format($pedido['total'], 2, ',', '.'); ?></strong></td>
                        </tr>
                    </tfoot>
                </table>
                
                <div style="text-align: center; margin-top: 20px;">
                    <a href="index.php" class="btn">Volver a la Tienda</a>
                    <a href="#" onclick="window.print()" class="btn btn-outline">Imprimir Pedido</a>
                </div>
            </div>
        <?php else: ?>
            <div class="confirmation-card" style="text-align: center;">
                <div class="confirmation-icon" style="color: #f44336;">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h1 class="confirmation-title">Pedido No Encontrado</h1>
                <p class="confirmation-message">
                    Lo sentimos, no pudimos encontrar la información del pedido solicitado.
                </p>
                <a href="index.php" class="btn">Volver a la Tienda</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>

