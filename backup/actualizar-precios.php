<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$productos_actualizados = 0;

// Procesar la actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar'])) {
    $multiplicador = (float)$_POST['multiplicador'];
    
    if ($multiplicador <= 0) {
        $mensaje = 'El multiplicador debe ser mayor que cero.';
    } else {
        // Obtener todos los productos
        $productos = obtenerResultados("SELECT id, nombre, precio FROM productos");
        
        // Registrar los cambios
        $registro = "Actualización de precios - " . date('Y-m-d H:i:s') . "\n";
        $registro .= "Multiplicador: " . $multiplicador . "\n";
        $registro .= "----------------------------------------\n";
        
        foreach ($productos as $producto) {
            $id = $producto['id'];
            $precio_antiguo = $producto['precio'];
            $precio_nuevo = round($precio_antiguo * $multiplicador, 2);
            
            // Actualizar precio
            query("UPDATE productos SET precio = $precio_nuevo WHERE id = $id");
            
            // Registrar cambio
            $registro .= "ID: $id - " . $producto['nombre'] . "\n";
            $registro .= "Precio antiguo: $" . number_format($precio_antiguo, 2, ',', '.') . "\n";
            $registro .= "Precio nuevo: $" . number_format($precio_nuevo, 2, ',', '.') . "\n";
            $registro .= "----------------------------------------\n";
            
            $productos_actualizados++;
        }
        
        // Guardar registro en archivo
        $archivo_registro = '../logs/actualizacion_precios_' . date('Y-m-d_H-i-s') . '.txt';
        
        // Crear directorio si no existe
        if (!file_exists('../logs')) {
            mkdir('../logs', 0777, true);
        }
        
        file_put_contents($archivo_registro, $registro);
        
        $mensaje = "Se actualizaron los precios de $productos_actualizados productos correctamente. Se ha guardado un registro en $archivo_registro";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Actualizar Precios - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos similares a los otros archivos admin */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
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
        }
        .logo h1 {
            margin: 0;
            font-size: 24px;
        }
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .btn {
            background-color: #eec8a3;
            color: #945a42;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }
        .btn:hover {
            background-color: #e5b78e;
        }
        .page-header {
            margin-bottom: 20px;
        }
        .page-title {
            color: #945a42;
            margin: 0 0 10px 0;
        }
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .warning {
            background-color: #fff3e0;
            color: #e65100;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .submit-btn {
            background-color: #945a42;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }
        .submit-btn:hover {
            background-color: #7a4a37;
        }
        .preview-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .preview-table th, .preview-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .preview-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>BEAR SHOP - ADMIN</h1>
            </div>
            <div class="user-info">
                <a href="productos.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Volver a Productos
                </a>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Actualizar Precios de Productos</h2>
            <p>Actualiza los precios de todos los productos aplicando un multiplicador.</p>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="message">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!isset($_POST['confirmar'])): ?>
            <div class="warning">
                <p><strong>¡Atención!</strong> Esta acción actualizará los precios de TODOS los productos en la base de datos.</p>
                <p>Se recomienda hacer una copia de seguridad de la base de datos antes de continuar.</p>
            </div>
            
            <div class="form-container">
                <form method="POST">
                    <div class="form-group">
                        <label for="multiplicador">Multiplicador de Precio</label>
                        <input type="number" id="multiplicador" name="multiplicador" step="0.01" value="2.2" min="0.01" required>
                        <small>Ejemplo: Un multiplicador de 2.2 convertirá un precio de $100 en $220.</small>
                    </div>
                    
                    <h3>Vista Previa</h3>
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>Precio Actual</th>
                                <th>Nuevo Precio (x2.2)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $ejemplos = [100, 500, 1000, 5000, 10000];
                            foreach ($ejemplos as $ejemplo): 
                                $nuevo = $ejemplo * 2.2;
                            ?>
                            <tr>
                                <td>$<?php echo number_format($ejemplo, 2, ',', '.'); ?></td>
                                <td>$<?php echo number_format($nuevo, 2, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <button type="submit" name="confirmar" class="submit-btn" onclick="return confirm('¿Estás seguro de actualizar TODOS los precios? Esta acción no se puede deshacer.')">
                        <i class="fas fa-sync-alt"></i> Actualizar Todos los Precios
                    </button>
                </form>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_POST['confirmar']) && $productos_actualizados > 0): ?>
            <div class="form-container">
                <a href="productos.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Volver a Productos
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>