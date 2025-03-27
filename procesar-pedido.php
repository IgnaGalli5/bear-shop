<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

// Iniciar o continuar sesión
session_start();

// Función para registrar errores en un archivo de log
function registrarError($mensaje) {
    $archivo_log = 'logs/errores_pedidos.log';
    $directorio = dirname($archivo_log);
    
    // Crear directorio si no existe
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    // Registrar error con fecha y hora
    $fecha = date('Y-m-d H:i:s');
    $mensaje_log = "[$fecha] $mensaje\n";
    file_put_contents($archivo_log, $mensaje_log, FILE_APPEND);
}

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Verificar si hay productos en el carrito
        if (!isset($_SESSION['carrito']) || empty($_SESSION['carrito'])) {
            // Registrar error
            registrarError("Intento de enviar pedido con carrito vacío");
            
            // Redirigir con mensaje de error
            header('Location: carrito.php?error=El carrito está vacío');
            exit;
        }
        
        // Obtener datos del formulario
        $cliente_id = isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : 1; // Cliente por defecto si no hay sesión
        $fecha = date('Y-m-d H:i:s');
        $estado = 'pendiente';
        $metodo_pago = isset($_POST['metodo_pago']) ? escapar($_POST['metodo_pago']) : 'Efectivo';
        $direccion_envio = isset($_POST['direccion']) ? escapar($_POST['direccion']) : '';
        $notas = isset($_POST['notas']) ? escapar($_POST['notas']) : '';
        
        // Calcular total del pedido
        $total = 0;
        foreach ($_SESSION['carrito'] as $producto) {
            $total += $producto['precio'] * $producto['cantidad'];
        }
        
        // Insertar pedido en la base de datos
        $sql_pedido = "INSERT INTO pedidos (cliente_id, fecha, estado, total, metodo_pago, direccion_envio, notas) 
                       VALUES ($cliente_id, '$fecha', '$estado', $total, '$metodo_pago', '$direccion_envio', '$notas')";
        
        // Ejecutar consulta y verificar resultado
        if (!query($sql_pedido)) {
            // Registrar error
            registrarError("Error al insertar pedido: " . mysqli_error(conectarDB()));
            
            // Redirigir con mensaje de error
            header('Location: carrito.php?error=Error al procesar el pedido');
            exit;
        }
        
        // Obtener ID del pedido insertado
        $pedido_id = mysqli_insert_id(conectarDB());
        
        // Insertar items del pedido
        foreach ($_SESSION['carrito'] as $producto) {
            $producto_id = (int)$producto['id'];
            $cantidad = (int)$producto['cantidad'];
            $precio_unitario = (float)$producto['precio'];
            $subtotal = $precio_unitario * $cantidad;
            
            $sql_item = "INSERT INTO pedido_items (pedido_id, producto_id, cantidad, precio_unitario, subtotal) 
                         VALUES ($pedido_id, $producto_id, $cantidad, $precio_unitario, $subtotal)";
            
            // Ejecutar consulta
            if (!query($sql_item)) {
                // Registrar error
                registrarError("Error al insertar item de pedido: " . mysqli_error(conectarDB()));
                
                // No interrumpimos el proceso, seguimos con los demás items
            }
            
            // Actualizar stock si existe la columna
            $resultado_columna = query("SHOW COLUMNS FROM productos LIKE 'stock'");
            if ($resultado_columna->num_rows > 0) {
                query("UPDATE productos SET stock = stock - $cantidad WHERE id = $producto_id");
            }
        }
        
        // Cambiar estado del pedido a completado
        query("UPDATE pedidos SET estado = 'completado' WHERE id = $pedido_id");
        
        // Vaciar carrito
        $_SESSION['carrito'] = [];
        
        // Guardar ID del pedido en la sesión para mostrar confirmación
        $_SESSION['ultimo_pedido'] = $pedido_id;
        
        // Redirigir a página de confirmación
        header('Location: confirmacion-pedido.php?id=' . $pedido_id);
        exit;
        
    } catch (Exception $e) {
        // Registrar error
        registrarError("Excepción al procesar pedido: " . $e->getMessage());
        
        // Redirigir con mensaje de error
        header('Location: carrito.php?error=Error inesperado al procesar el pedido');
        exit;
    }
} else {
    // Si se intenta acceder directamente a este archivo sin enviar el formulario
    header('Location: carrito.php');
    exit;
}
?>

