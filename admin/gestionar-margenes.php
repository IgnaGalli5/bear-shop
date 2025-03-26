<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$mensaje = '';
$error = '';

// Obtener categorías únicas
$categorias = obtenerResultados("SELECT DISTINCT categoria FROM productos ORDER BY categoria");

// Obtener márgenes existentes
$margenes = obtenerResultados("SELECT * FROM margenes_categoria ORDER BY categoria");

// Procesar formulario para actualizar márgenes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Actualizar márgenes por categoría
    if (isset($_POST['actualizar_margenes'])) {
        // Iniciar registro de cambios
        $registro = "Actualización de márgenes por categoría - " . date('Y-m-d H:i:s') . "\n";
        $registro .= "----------------------------------------\n";
        
        foreach ($_POST['multiplicador'] as $categoria => $multiplicador) {
            $categoria = escapar($categoria);
            $multiplicador = (float)$multiplicador;
            
            if ($multiplicador < 1.0) {
                $error = "El multiplicador debe ser mayor o igual a 1.0";
                break;
            }
            
            // Obtener el multiplicador anterior
            $margen_anterior = obtenerResultados("SELECT multiplicador FROM margenes_categoria WHERE categoria = '$categoria'");
            $mult_anterior = !empty($margen_anterior) ? $margen_anterior[0]['multiplicador'] : 'N/A';
            
            // Registrar cambio
            $registro .= "Categoría: $categoria\n";
            $registro .= "Multiplicador anterior: $mult_anterior\n";
            $registro .= "Nuevo multiplicador: $multiplicador\n";
            $registro .= "----------------------------------------\n";
            
            // Verificar si ya existe un registro para esta categoría
            $existe = obtenerResultados("SELECT id FROM margenes_categoria WHERE categoria = '$categoria'");
            
            if (empty($existe)) {
                // Insertar nuevo registro
                query("INSERT INTO margenes_categoria (categoria, multiplicador) VALUES ('$categoria', $multiplicador)");
            } else {
                // Actualizar registro existente
                query("UPDATE margenes_categoria SET multiplicador = $multiplicador WHERE categoria = '$categoria'");
            }
            
           // Actualizar precios de productos en esta categoría
                query("UPDATE productos SET 
                multiplicador = $multiplicador,
                precio = precio_costo * $multiplicador
                WHERE categoria = '$categoria' AND precio_costo IS NOT NULL");
                        }
        
        if (!$error) {
            // Guardar registro en archivo
            guardarRegistroCambios($registro, 'margenes_categoria');
            
            $mensaje = "Márgenes actualizados correctamente";
            
            // Recargar márgenes
            $margenes = obtenerResultados("SELECT * FROM margenes_categoria ORDER BY categoria");
        }
    }
    
    // Actualizar margen global
    if (isset($_POST['actualizar_global'])) {
        $multiplicador_global = (float)$_POST['multiplicador_global'];
        
        if ($multiplicador_global >= 1.0) {
            // Iniciar registro de cambios
            $registro = "Actualización de margen global - " . date('Y-m-d H:i:s') . "\n";
            $registro .= "Multiplicador global: $multiplicador_global\n";
            $registro .= "----------------------------------------\n";
            
            // Actualizar todos los productos
                query("UPDATE productos SET 
                multiplicador = $multiplicador_global,
                precio = precio_costo * $multiplicador_global
                WHERE precio_costo IS NOT NULL");
            
            // Actualizar todas las categorías
            foreach ($categorias as $cat) {
                $categoria = escapar($cat['categoria']);
                
                // Obtener el multiplicador anterior
                $margen_anterior = obtenerResultados("SELECT multiplicador FROM margenes_categoria WHERE categoria = '$categoria'");
                $mult_anterior = !empty($margen_anterior) ? $margen_anterior[0]['multiplicador'] : 'N/A';
                
                // Registrar cambio
                $registro .= "Categoría: $categoria\n";
                $registro .= "Multiplicador anterior: $mult_anterior\n";
                $registro .= "Nuevo multiplicador: $multiplicador_global\n";
                $registro .= "----------------------------------------\n";
                
                // Verificar si ya existe un registro para esta categoría
                $existe = obtenerResultados("SELECT id FROM margenes_categoria WHERE categoria = '$categoria'");
                
                if (empty($existe)) {
                    // Insertar nuevo registro
                    query("INSERT INTO margenes_categoria (categoria, multiplicador) VALUES ('$categoria', $multiplicador_global)");
                } else {
                    // Actualizar registro existente
                    query("UPDATE margenes_categoria SET multiplicador = $multiplicador_global WHERE categoria = '$categoria'");
                }
            }
            
            // Guardar registro en archivo
            guardarRegistroCambios($registro, 'margen_global');
            
            $mensaje = "Margen global aplicado correctamente";
            
            // Recargar márgenes
            $margenes = obtenerResultados("SELECT * FROM margenes_categoria ORDER BY categoria");
        } else {
            $error = "El multiplicador global debe ser mayor o igual a 1.0";
        }
    }
    
    // Actualizar precio individual de producto
    if (isset($_POST['actualizar_precio_producto'])) {
        $producto_id = (int)$_POST['producto_id'];
        $nuevo_precio = (float)$_POST['nuevo_precio'];
        $precio_costo = (float)$_POST['precio_costo'];
        
        if ($nuevo_precio <= 0) {
            $error = "El precio debe ser mayor que cero";
        } else {
            // Obtener información del producto
            $producto = obtenerResultados("SELECT * FROM productos WHERE id = $producto_id")[0];
            
            // Calcular nuevo multiplicador si hay precio de costo
            $nuevo_multiplicador = $precio_costo > 0 ? $nuevo_precio / $precio_costo : 0;
            
            // Iniciar registro de cambios
            $registro = "Actualización de precio individual - " . date('Y-m-d H:i:s') . "\n";
            $registro .= "Producto: {$producto['nombre']} (ID: $producto_id)\n";
            $registro .= "Precio anterior: {$producto['precio']}\n";
            $registro .= "Nuevo precio: $nuevo_precio\n";
            
            if ($precio_costo > 0) {
                $registro .= "Precio de costo: $precio_costo\n";
                $registro .= "Nuevo multiplicador: $nuevo_multiplicador\n";
            }
            
            $registro .= "----------------------------------------\n";
            
            // Actualizar precio del producto
                if ($precio_costo > 0) {
                    query("UPDATE productos SET 
                        precio = $nuevo_precio,
                        precio_costo = $precio_costo,
                        multiplicador = $nuevo_multiplicador
                        WHERE id = $producto_id");
                } else {
                    query("UPDATE productos SET 
                        precio = $nuevo_precio
                        WHERE id = $producto_id");
                }
            
            // Guardar registro en archivo
            guardarRegistroCambios($registro, 'precio_individual');
            
            $mensaje = "Precio del producto actualizado correctamente";
        }
    }
    
    // Recalcular todos los precios basados en los márgenes actuales
    if (isset($_POST['recalcular_precios'])) {
        $actualizados = 0;
        
        // Iniciar registro de cambios
        $registro = "Recálculo de precios - " . date('Y-m-d H:i:s') . "\n";
        $registro .= "----------------------------------------\n";
        
        foreach ($categorias as $cat) {
            $categoria = escapar($cat['categoria']);
            
            // Obtener el multiplicador para esta categoría
            $margen_result = obtenerResultados("SELECT multiplicador FROM margenes_categoria WHERE categoria = '$categoria'");
            
            if (!empty($margen_result)) {
                $multiplicador = (float)$margen_result[0]['multiplicador'];
                
                // Obtener productos de esta categoría
                $productos_cat = obtenerResultados("SELECT id, nombre, precio, precio_costo FROM productos WHERE categoria = '$categoria' AND precio_costo IS NOT NULL");
                
                foreach ($productos_cat as $producto) {
                    $precio_anterior = $producto['precio'];
                    $nuevo_precio = $producto['precio_costo'] * $multiplicador;
                    
                    // Registrar cambio
                    $registro .= "Producto: {$producto['nombre']} (ID: {$producto['id']})\n";
                    $registro .= "Categoría: $categoria\n";
                    $registro .= "Precio anterior: $precio_anterior\n";
                    $registro .= "Nuevo precio: $nuevo_precio\n";
                    $registro .= "----------------------------------------\n";
                }
                
                /// Actualizar precios de productos en esta categoría
                    $result = query("UPDATE productos SET 
                    precio = precio_costo * $multiplicador
                    WHERE categoria = '$categoria' AND precio_costo IS NOT NULL");
            }
        }
        
        // Guardar registro en archivo
        guardarRegistroCambios($registro, 'recalculo_precios');
        
        $mensaje = "Se han recalculado los precios de $actualizados productos";
    }
}

// Función para guardar registro de cambios
function guardarRegistroCambios($registro, $tipo) {
    // Crear directorio si no existe
    if (!file_exists('../logs')) {
        mkdir('../logs', 0777, true);
    }
    
    // Guardar registro en archivo
    $archivo_registro = '../logs/' . $tipo . '_' . date('Y-m-d_H-i-s') . '.txt';
    file_put_contents($archivo_registro, $registro);
    
    // Guardar referencia en la base de datos para el historial
    $admin_id = $_SESSION['admin_id'];
    $tipo_accion = escapar($tipo);
    $archivo = escapar($archivo_registro);
    
    query("INSERT INTO historial_precios (admin_id, tipo_accion, archivo_log, fecha) 
           VALUES ($admin_id, '$tipo_accion', '$archivo', NOW())");
}

// Convertir márgenes a un array asociativo para fácil acceso
$margenes_por_categoria = [];
foreach ($margenes as $margen) {
    $margenes_por_categoria[$margen['categoria']] = $margen['multiplicador'];
}

// Obtener estadísticas de productos
$stats = obtenerResultados("
    SELECT 
        COUNT(*) as total_productos,
        COUNT(CASE WHEN precio_costo IS NULL THEN 1 END) as sin_costo,
        COUNT(CASE WHEN precio_costo IS NOT NULL THEN 1 END) as con_costo
    FROM productos
")[0];

// Obtener productos para edición individual (limitado a 20 para no sobrecargar la página)
$productos_edicion = obtenerResultados("
    SELECT id, nombre, categoria, precio, precio_costo, multiplicador
    FROM productos
    ORDER BY nombre
    LIMIT 20
");

// Obtener historial de cambios de precios (últimos 10)
$historial = obtenerResultados("
    SELECT h.*
    FROM historial_precios h
    ORDER BY h.fecha DESC
    LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Márgenes - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos base */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #945a42;
            color: white;
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
            max-width: 1200px;
            margin: 0 auto;
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
        
        /* Botones y controles */
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
            transition: all 0.2s ease;
        }
        .btn:hover {
            background-color: #e5b78e;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #945a42;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
        }
        .btn-primary:hover {
            background-color: #7a4a37;
        }
        .btn-outline {
            background-color: transparent;
            border: 1px solid #945a42;
            color: #945a42;
        }
        .btn-outline:hover {
            background-color: #f9f1e9;
        }
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
        
        /* Tarjetas y contenedores */
        .page-header {
            margin-bottom: 20px;
        }
        .page-title {
            color: #945a42;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            margin-bottom: 20px;
        }
        .card-title {
            color: #945a42;
            margin: 0 0 10px 0;
            font-size: 22px;
        }
        .card-description {
            color: #666;
            margin: 0;
        }
        
        /* Formularios */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
        }
        .form-control:focus {
            border-color: #945a42;
            outline: none;
            box-shadow: 0 0 0 2px rgba(148, 90, 66, 0.2);
        }
        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            align-items: center;
        }
        .form-row label {
            flex: 1;
            margin-bottom: 0;
        }
        .form-row .form-control {
            flex: 1;
        }
        
        /* Mensajes */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-danger {
            background-color: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        .alert-info {
            background-color: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }
        
        /* Tabs */
        .tabs {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s ease;
        }
        .tab:hover {
            background-color: #f9f9f9;
        }
        .tab.active {
            border-bottom: 2px solid #945a42;
            color: #945a42;
            font-weight: bold;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        
        /* Tablas */
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f9f9f9;
            font-weight: bold;
            color: #333;
        }
        .table tr:hover {
            background-color: #f5f5f5;
        }
        
        /* Elementos específicos */
        .categoria-header {
            text-transform: capitalize;
            color: #945a42;
            margin-top: 0;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .margin-preview {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            margin-top: 10px;
            border-left: 4px solid #945a42;
        }
        .margin-preview p {
            margin: 5px 0;
        }
        .margin-preview .example {
            font-style: italic;
            color: #666;
        }
        .stats-container {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            background-color: #fff;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 5px 0;
            color: #945a42;
        }
        .stat-card p {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
        }
        .search-box {
            margin-bottom: 20px;
        }
        .search-box input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 600px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: #333;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        .badge-warning {
            background-color: #fff3e0;
            color: #e65100;
        }
        .badge-info {
            background-color: #e3f2fd;
            color: #1565c0;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 10px;
            }
            .form-row label, .form-row .form-control {
                flex: none;
                width: 100%;
            }
            .stats-container {
                flex-direction: column;
                gap: 10px;
            }
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
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Gestión de Márgenes de Ganancia</h2>
            <p>Configura los multiplicadores para calcular precios de venta a partir de los costos.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <!-- Estadísticas -->
        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Productos</h3>
                <p><?php echo $stats['total_productos']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Con Precio de Costo</h3>
                <p><?php echo $stats['con_costo']; ?></p>
            </div>
            <div class="stat-card">
                <h3>Sin Precio de Costo</h3>
                <p><?php echo $stats['sin_costo']; ?></p>
            </div>
        </div>
        
        <div class="alert alert-info">
            <h4 style="margin-top: 0;"><i class="fas fa-info-circle"></i> ¿Cómo funcionan los márgenes?</h4>
            <p style="margin-bottom: 0;">El precio de venta se calcula multiplicando el precio de costo por el multiplicador. Por ejemplo, si el costo es $100 y el multiplicador es 2.0, el precio de venta será $200.</p>
        </div>
        
        <!-- Tabs de navegación -->
        <div class="tabs">
            <div class="tab active" data-tab="global">Margen Global</div>
            <div class="tab" data-tab="categorias">Márgenes por Categoría</div>
            <div class="tab" data-tab="productos">Precios Individuales</div>
            <div class="tab" data-tab="historial">Historial de Cambios</div>
        </div>
        
        <!-- Tab de Margen Global -->
        <div class="tab-content active" id="tab-global">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actualizar Margen Global</h3>
                    <p class="card-description">Aplica el mismo multiplicador a todas las categorías y productos.</p>
                </div>
                
                <form method="POST">
                    <div class="form-row">
                        <label for="multiplicador_global">Multiplicador Global:</label>
                        <input type="number" id="multiplicador_global" name="multiplicador_global" class="form-control" step="0.01" min="1.0" value="2.0" required>
                    </div>
                    
                    <div class="margin-preview">
                        <p>Con un multiplicador de <span id="preview_multiplicador">2.0</span>:</p>
                        <p class="example">Un producto con costo de $1000 se venderá a $<span id="preview_precio">2000</span></p>
                        <p class="example">Margen de ganancia: <span id="preview_porcentaje">100</span>%</p>
                    </div>
                    
                    <button type="submit" name="actualizar_global" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Aplicar Margen Global
                    </button>
                </form>
            </div>
        </div>
        
        <!-- Tab de Márgenes por Categoría -->
        <div class="tab-content" id="tab-categorias">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Márgenes por Categoría</h3>
                    <p class="card-description">Configura multiplicadores específicos para cada categoría de productos.</p>
                </div>
                
                <form method="POST">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Multiplicador</th>
                                <th>Ejemplo</th>
                                <th>Margen</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $categoria): 
                                $cat = $categoria['categoria'];
                                $multiplicador = isset($margenes_por_categoria[$cat]) ? $margenes_por_categoria[$cat] : 2.0;
                                $precio_ejemplo = 1000 * $multiplicador;
                                $porcentaje = ($multiplicador - 1) * 100;
                            ?>
                                <tr>
                                    <td style="text-transform: capitalize;"><?php echo $cat; ?></td>
                                    <td>
                                        <input type="number" 
                                               name="multiplicador[<?php echo $cat; ?>]" 
                                               class="form-control multiplicador-categoria" 
                                               step="0.01" 
                                               min="1.0" 
                                               value="<?php echo $multiplicador; ?>" 
                                               data-categoria="<?php echo $cat; ?>"
                                               required>
                                    </td>
                                    <td>
                                        $1000 → $<span class="precio-ejemplo" id="precio_<?php echo $cat; ?>"><?php echo number_format($precio_ejemplo, 0, '.', ''); ?></span>
                                    </td>
                                    <td>
                                        <span class="porcentaje" id="porcentaje_<?php echo $cat; ?>"><?php echo number_format($porcentaje, 0, '.', ''); ?></span>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div style="display: flex; gap: 10px; margin-top: 20px;">
                        <button type="submit" name="actualizar_margenes" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Márgenes por Categoría
                        </button>
                        
                        <button type="submit" name="recalcular_precios" class="btn btn-outline">
                            <i class="fas fa-calculator"></i> Recalcular Todos los Precios
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Tab de Precios Individuales -->
        <div class="tab-content" id="tab-productos">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Editar Precios Individuales</h3>
                    <p class="card-description">Modifica los precios de productos específicos.</p>
                </div>
                
                <div class="search-box">
                    <input type="text" id="buscar_producto" placeholder="Buscar producto por nombre..." class="form-control">
                </div>
                
                <table class="table" id="tabla_productos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio Actual</th>
                            <th>Precio Costo</th>
                            <th>Multiplicador</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos_edicion as $producto): ?>
                            <tr>
                                <td><?php echo $producto['nombre']; ?></td>
                                <td style="text-transform: capitalize;"><?php echo $producto['categoria']; ?></td>
                                <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php if ($producto['precio_costo']): ?>
                                        $<?php echo number_format($producto['precio_costo'], 2, ',', '.'); ?>
                                    <?php else: ?>
                                        <span class="badge badge-warning">No definido</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($producto['multiplicador']): ?>
                                        <?php echo $producto['multiplicador']; ?>x
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline editar-precio" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo $producto['nombre']; ?>" data-precio="<?php echo $producto['precio']; ?>" data-costo="<?php echo $producto['precio_costo']; ?>">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="card-description">
                    <i class="fas fa-info-circle"></i> Se muestran los primeros 20 productos. Use el buscador para encontrar productos específicos.
                </p>
            </div>
        </div>
        
        <!-- Tab de Historial de Cambios -->
        <div class="tab-content" id="tab-historial">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Historial de Cambios de Precios</h3>
                    <p class="card-description">Registro de todas las modificaciones realizadas a los precios y márgenes.</p>
                </div>
                
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Administrador</th>
                            <th>Tipo de Acción</th>
                            <th>Detalles</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($historial)): ?>
                            <tr>
                                <td colspan="4" class="text-center">No hay registros de cambios de precios.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($historial as $registro): 
                                $tipo_badge = '';
                                $tipo_texto = '';
                                
                                switch ($registro['tipo_accion']) {
                                    case 'margen_global':
                                        $tipo_badge = 'badge-info';
                                        $tipo_texto = 'Actualización de margen global';
                                        break;
                                    case 'margenes_categoria':
                                        $tipo_badge = 'badge-success';
                                        $tipo_texto = 'Actualización de márgenes por categoría';
                                        break;
                                    case 'precio_individual':
                                        $tipo_badge = 'badge-warning';
                                        $tipo_texto = 'Actualización de precio individual';
                                        break;
                                    case 'recalculo_precios':
                                        $tipo_badge = 'badge-info';
                                        $tipo_texto = 'Recálculo de todos los precios';
                                        break;
                                    default:
                                        $tipo_badge = 'badge-info';
                                        $tipo_texto = $registro['tipo_accion'];
                                }
                            ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($registro['fecha'])); ?></td>
                                    <td>Admin #<?php echo $registro['admin_id']; ?></td>
                                    <td><span class="badge <?php echo $tipo_badge; ?>"><?php echo $tipo_texto; ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline ver-detalles" data-archivo="<?php echo $registro['archivo_log']; ?>">
                                            <i class="fas fa-eye"></i> Ver Detalles
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                
                <a href="historial-completo.php" class="btn btn-outline">
                    <i class="fas fa-history"></i> Ver Historial Completo
                </a>
            </div>
        </div>
    </div>
    
    <!-- Modal para editar precio individual -->
    <div id="modal_editar_precio" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Editar Precio de Producto</h3>
            
            <form method="POST">
                <input type="hidden" id="producto_id" name="producto_id">
                
                <div class="form-group">
                    <label>Producto:</label>
                    <p id="modal_nombre_producto" style="font-weight: bold;"></p>
                </div>
                
                <div class="form-group">
                    <label for="precio_costo">Precio de Costo:</label>
                    <input type="number" id="precio_costo" name="precio_costo" class="form-control" step="0.01" min="0">
                    <small>Dejar en 0 si no se conoce el costo.</small>
                </div>
                
                <div class="form-group">
                    <label for="nuevo_precio">Nuevo Precio de Venta:</label>
                    <input type="number" id="nuevo_precio" name="nuevo_precio" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="margin-preview" id="modal_preview" style="display: none;">
                    <p>Con un precio de costo de $<span id="preview_costo">0</span> y un precio de venta de $<span id="preview_venta">0</span>:</p>
                    <p class="example">Multiplicador resultante: <span id="preview_mult">0</span>x</p>
                    <p class="example">Margen de ganancia: <span id="preview_margen">0</span>%</p>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" name="actualizar_precio_producto" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <button type="button" class="btn btn-outline cerrar-modal">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal para ver detalles de cambio -->
    <div id="modal_detalles" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detalles del Cambio</h3>
            
            <pre id="detalles_contenido" style="background-color: #f5f5f5; padding: 15px; border-radius: 4px; overflow: auto; max-height: 400px;"></pre>
            
            <button type="button" class="btn btn-outline cerrar-modal">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tabs de navegación
            const tabs = document.querySelectorAll('.tab');
            const tabContents = document.querySelectorAll('.tab-content');
            
            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabId = this.getAttribute('data-tab');
                    
                    // Desactivar todas las tabs y contenidos
                    tabs.forEach(t => t.classList.remove('active'));
                    tabContents.forEach(c => c.classList.remove('active'));
                    
                    // Activar la tab seleccionada y su contenido
                    this.classList.add('active');
                    document.getElementById('tab-' + tabId).classList.add('active');
                });
            });
            
            // Para el multiplicador global
            const multiplicadorGlobal = document.getElementById('multiplicador_global');
            const previewMultiplicador = document.getElementById('preview_multiplicador');
            const previewPrecio = document.getElementById('preview_precio');
            const previewPorcentaje = document.getElementById('preview_porcentaje');
            
            multiplicadorGlobal.addEventListener('input', function() {
                const valor = parseFloat(this.value) || 0;
                previewMultiplicador.textContent = valor.toFixed(2);
                previewPrecio.textContent = (valor * 1000).toFixed(0);
                previewPorcentaje.textContent = ((valor - 1) * 100).toFixed(0);
            });
            
            // Para los multiplicadores por categoría
            const multiplicadoresCategorias = document.querySelectorAll('.multiplicador-categoria');
            
            multiplicadoresCategorias.forEach(function(input) {
                input.addEventListener('input', function() {
                    const categoria = this.getAttribute('data-categoria');
                    const valor = parseFloat(this.value) || 0;
                    
                    const precioEjemplo = document.getElementById('precio_' + categoria);
                    const porcentaje = document.getElementById('porcentaje_' + categoria);
                    
                    if (precioEjemplo && porcentaje) {
                        precioEjemplo.textContent = (valor * 1000).toFixed(0);
                        porcentaje.textContent = ((valor - 1) * 100).toFixed(0);
                    }
                });
            });
            
            // Búsqueda de productos
            const buscarProducto = document.getElementById('buscar_producto');
            const tablaProductos = document.getElementById('tabla_productos');
            
            buscarProducto.addEventListener('input', function() {
                const termino = this.value.toLowerCase();
                const filas = tablaProductos.querySelectorAll('tbody tr');
                
                filas.forEach(function(fila) {
                    const nombre = fila.cells[0].textContent.toLowerCase();
                    const categoria = fila.cells[1].textContent.toLowerCase();
                    
                    if (nombre.includes(termino) || categoria.includes(termino)) {
                        fila.style.display = '';
                    } else {
                        fila.style.display = 'none';
                    }
                });
            });
            
            // Modal para editar precio
            const modal = document.getElementById('modal_editar_precio');
            const modalDetalles = document.getElementById('modal_detalles');
            const botonesEditar = document.querySelectorAll('.editar-precio');
            const botonesVerDetalles = document.querySelectorAll('.ver-detalles');
            const botonesCerrar = document.querySelectorAll('.close, .cerrar-modal');
            
            // Abrir modal de edición
            botonesEditar.forEach(function(boton) {
                boton.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const nombre = this.getAttribute('data-nombre');
                    const precio = this.getAttribute('data-precio');
                    const costo = this.getAttribute('data-costo');
                    
                    document.getElementById('producto_id').value = id;
                    document.getElementById('modal_nombre_producto').textContent = nombre;
                    document.getElementById('nuevo_precio').value = precio;
                    document.getElementById('precio_costo').value = costo || '';
                    
                    actualizarPreviewModal();
                    
                    modal.style.display = 'block';
                });
            });
            
            // Abrir modal de detalles
            botonesVerDetalles.forEach(function(boton) {
                boton.addEventListener('click', function() {
                    const archivo = this.getAttribute('data-archivo');
                    
                    // Cargar contenido del archivo
                    fetch('ver-log.php?archivo=' + encodeURIComponent(archivo))
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('detalles_contenido').textContent = data;
                            modalDetalles.style.display = 'block';
                        })
                        .catch(error => {
                            alert('Error al cargar los detalles: ' + error);
                        });
                });
            });
            
            // Cerrar modales
            botonesCerrar.forEach(function(boton) {
                boton.addEventListener('click', function() {
                    modal.style.display = 'none';
                    modalDetalles.style.display = 'none';
                });
            });
            
            // Cerrar modal al hacer clic fuera
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
                if (event.target == modalDetalles) {
                    modalDetalles.style.display = 'none';
                }
            });
            
            // Actualizar preview en el modal
            const precioCosto = document.getElementById('precio_costo');
            const nuevoPrecio = document.getElementById('nuevo_precio');
            const modalPreview = document.getElementById('modal_preview');
            const previewCosto = document.getElementById('preview_costo');
            const previewVenta = document.getElementById('preview_venta');
            const previewMult = document.getElementById('preview_mult');
            const previewMargen = document.getElementById('preview_margen');
            
            function actualizarPreviewModal() {
                const costo = parseFloat(precioCosto.value) || 0;
                const venta = parseFloat(nuevoPrecio.value) || 0;
                
                if (costo > 0 && venta > 0) {
                    const mult = venta / costo;
                    const margen = (mult - 1) * 100;
                    
                    previewCosto.textContent = costo.toFixed(2);
                    previewVenta.textContent = venta.toFixed(2);
                    previewMult.textContent = mult.toFixed(2);
                    previewMargen.textContent = margen.toFixed(0);
                    
                    modalPreview.style.display = 'block';
                } else {
                    modalPreview.style.display = 'none';
                }
            }
            
            precioCosto.addEventListener('input', actualizarPreviewModal);
            nuevoPrecio.addEventListener('input', actualizarPreviewModal);
        });
    </script>
</body>
</html>