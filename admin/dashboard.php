<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Obtener estadísticas generales
$total_productos = obtenerResultados("SELECT COUNT(*) as total FROM productos")[0]['total'];
$total_categorias = obtenerResultados("SELECT COUNT(DISTINCT categoria) as total FROM productos")[0]['total'];

// Verificar si existe la tabla pedidos
$tabla_pedidos_existe = false;
$resultado = query("SHOW TABLES LIKE 'pedidos'");
if ($resultado->num_rows > 0) {
    $tabla_pedidos_existe = true;
    
    // Obtener estadísticas de ventas
    $total_ventas = obtenerResultados("SELECT COUNT(*) as total FROM pedidos WHERE estado = 'completado'")[0]['total'];
    $ingresos_totales = obtenerResultados("SELECT SUM(total) as total FROM pedidos WHERE estado = 'completado'")[0]['total'];
    
    // Ventas del mes actual
    $mes_actual = date('n');
    $anio_actual = date('Y');
    $ventas_mes = obtenerResultados("SELECT COUNT(*) as total FROM pedidos WHERE MONTH(fecha) = $mes_actual AND YEAR(fecha) = $anio_actual AND estado = 'completado'")[0]['total'];
    $ingresos_mes = obtenerResultados("SELECT SUM(total) as total FROM pedidos WHERE MONTH(fecha) = $mes_actual AND YEAR(fecha) = $anio_actual AND estado = 'completado'")[0]['total'];
    
    // Productos más vendidos
    $productos_mas_vendidos = obtenerResultados("
        SELECT 
            p.nombre,
            p.categoria,
            SUM(pi.cantidad) as total_vendido
        FROM 
            pedido_items pi
        JOIN 
            productos p ON pi.producto_id = p.id
        JOIN 
            pedidos pe ON pi.pedido_id = pe.id
        WHERE 
            pe.estado = 'completado'
        GROUP BY 
            p.id
        ORDER BY 
            total_vendido DESC
        LIMIT 5
    ");
} else {
    // Valores por defecto si no existe la tabla
    $total_ventas = 0;
    $ingresos_totales = 0;
    $ventas_mes = 0;
    $ingresos_mes = 0;
    $productos_mas_vendidos = [];
}

// Verificar si existe la columna stock en la tabla productos
$columna_stock_existe = false;
$resultado_columna = query("SHOW COLUMNS FROM productos LIKE 'stock'");
if ($resultado_columna->num_rows > 0) {
    $columna_stock_existe = true;
    
    // Obtener productos con bajo stock (menos de 5 unidades)
    $productos_bajo_stock = obtenerResultados("
        SELECT id, nombre, stock 
        FROM productos 
        WHERE stock < 5 AND stock > 0
        ORDER BY stock ASC
        LIMIT 5
    ");

    // Obtener productos sin stock
    $productos_sin_stock = obtenerResultados("
        SELECT id, nombre 
        FROM productos 
        WHERE stock = 0
        LIMIT 5
    ");
} else {
    // Si no existe la columna stock
    $productos_bajo_stock = [];
    $productos_sin_stock = [];
}

// Verificar si existe la columna fecha_creacion en la tabla productos
$columna_fecha_existe = false;
$resultado_fecha = query("SHOW COLUMNS FROM productos LIKE 'fecha_creacion'");
if ($resultado_fecha->num_rows > 0) {
    $columna_fecha_existe = true;
    
    // Obtener productos recién agregados
    $productos_recientes = obtenerResultados("
        SELECT id, nombre, fecha_creacion 
        FROM productos 
        ORDER BY fecha_creacion DESC
        LIMIT 5
    ");
} else {
    // Si no existe la columna fecha_creacion
    $productos_recientes = obtenerResultados("
        SELECT id, nombre
        FROM productos 
        ORDER BY id DESC
        LIMIT 5
    ");
}

// Nombres de meses
$nombres_meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bear Shop</title>
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
        
        /* Tarjetas y contenedores */
        .page-header {
            margin-bottom: 20px;
        }
        .page-title {
            color: #945a42;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .welcome-message {
            color: #666;
            margin: 0;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .card-icon {
            width: 40px;
            height: 40px;
            background-color: #f9f1e9;
            color: #945a42;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 18px;
        }
        .card-title {
            color: #333;
            margin: 0;
            font-size: 16px;
        }
        .card-value {
            font-size: 24px;
            font-weight: bold;
            color: #945a42;
            margin: 10px 0;
        }
        .card-description {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        /* Secciones */
        .section {
            margin-bottom: 30px;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .section-title {
            color: #945a42;
            margin: 0;
            font-size: 20px;
        }
        .section-link {
            color: #945a42;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
        }
        .section-link:hover {
            text-decoration: underline;
        }
        
        /* Tablas */
        .table-container {
            overflow-x: auto;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background-color: #f9f9f9;
            font-weight: bold;
            color: #333;
        }
        .table tr:hover {
            background-color: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-warning {
            background-color: #fff3e0;
            color: #e65100;
        }
        .badge-danger {
            background-color: #ffebee;
            color: #c62828;
        }
        .badge-success {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        /* Flex layout */
        .flex-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .flex-column {
            flex: 1;
        }
        
        /* Estilos responsivos */
        .mobile-hidden {
            display: table-cell;
        }
        
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }
        
        /* Media queries para responsividad */
        @media screen and (max-width: 992px) {
            .header-content {
                flex-direction: column;
                padding: 10px;
            }
            
            .logo {
                margin-bottom: 10px;
            }
            
            .user-info {
                width: 100%;
                justify-content: center;
                flex-wrap: wrap;
            }
            
            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .dashboard-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .logo h1 {
                font-size: 20px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .flex-row {
                flex-direction: column;
            }
            
            .page-title {
                font-size: 24px;
            }
            
            .card-value {
                font-size: 20px;
            }
            
            /* Convertir tabla a formato móvil */
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 10px;
            }
            
            .table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 10px;
                text-align: right;
                border-bottom: 1px solid #eee;
            }
            
            .table tbody td:last-child {
                border-bottom: none;
            }
            
            .table tbody td:before {
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
                margin-right: auto;
            }
        }
        
        /* Botón flotante para móvil */
        .mobile-fab {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background-color: #945a42;
            color: white;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            text-decoration: none;
        }
        
        @media screen and (max-width: 768px) {
            .mobile-fab {
                display: flex;
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
                <a href="promociones.php" class="btn">
                    <i class="fas fa-tag"></i> Promociones
                </a>
                <a href="gestionar-margenes.php" class="btn">
                    <i class="fas fa-percentage"></i> Márgenes
                </a>
                <a href="logout.php" class="btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Dashboard</h2>
            <p class="welcome-message">Bienvenido al panel de administración de Bear Shop.</p>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <h3 class="card-title">Total Productos</h3>
                </div>
                <div class="card-value"><?php echo $total_productos; ?></div>
                <p class="card-description">Productos en catálogo</p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-tags"></i>
                    </div>
                    <h3 class="card-title">Categorías</h3>
                </div>
                <div class="card-value"><?php echo $total_categorias; ?></div>
                <p class="card-description">Categorías de productos</p>
            </div>
            
            <?php if ($tabla_pedidos_existe): ?>
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3 class="card-title">Ventas Totales</h3>
                </div>
                <div class="card-value"><?php echo $total_ventas; ?></div>
                <p class="card-description">Pedidos completados</p>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <h3 class="card-title">Ingresos Totales</h3>
                </div>
                <div class="card-value">$<?php echo number_format($ingresos_totales, 2, ',', '.'); ?></div>
                <p class="card-description">Ingresos por ventas</p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($tabla_pedidos_existe): ?>
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Ventas de <?php echo $nombres_meses[$mes_actual]; ?></h3>
                <a href="ventas-dashboard.php" class="section-link">Ver dashboard de ventas <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="flex-row">
                <div class="flex-column card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <h3 class="card-title">Pedidos del Mes</h3>
                    </div>
                    <div class="card-value"><?php echo $ventas_mes; ?></div>
                    <p class="card-description">Pedidos completados este mes</p>
                </div>
                
                <div class="flex-column card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3 class="card-title">Ingresos del Mes</h3>
                    </div>
                    <div class="card-value">$<?php echo number_format($ingresos_mes, 2, ',', '.'); ?></div>
                    <p class="card-description">Ingresos por ventas este mes</p>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Productos Más Vendidos</h3>
                <a href="ventas-dashboard.php" class="section-link">Ver todos <i class="fas fa-arrow-right"></i></a>
            </div>
            
            <div class="card">
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th>Categoría</th>
                                <th>Unidades Vendidas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($productos_mas_vendidos)): ?>
                                <tr>
                                    <td colspan="3" style="text-align: center;">No hay datos de ventas disponibles</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($productos_mas_vendidos as $producto): ?>
                                    <tr>
                                        <td data-label="Producto"><?php echo $producto['nombre']; ?></td>
                                        <td data-label="Categoría" style="text-transform: capitalize;"><?php echo $producto['categoria']; ?></td>
                                        <td data-label="Unidades Vendidas"><?php echo $producto['total_vendido']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="section">
            <div class="card" style="text-align: center; padding: 30px;">
                <i class="fas fa-chart-line" style="font-size: 48px; color: #945a42; margin-bottom: 20px;"></i>
                <h3 style="margin-bottom: 15px;">Configurar Sistema de Ventas</h3>
                <p style="margin-bottom: 20px;">Para habilitar el seguimiento de ventas y estadísticas, necesitas configurar las tablas de pedidos en la base de datos.</p>
                <a href="crear-columnas.php" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Configurar Sistema
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="flex-row">
            <div class="flex-column">
                <div class="section">
                    <div class="section-header">
                        <h3 class="section-title">Productos con Bajo Stock</h3>
                        <a href="productos.php" class="section-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="card">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <?php if ($columna_stock_existe): ?>
                                        <th>Stock</th>
                                        <th>Estado</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!$columna_stock_existe): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center;">La columna 'stock' no existe en la tabla productos</td>
                                        </tr>
                                    <?php elseif (empty($productos_bajo_stock) && empty($productos_sin_stock)): ?>
                                        <tr>
                                            <td colspan="3" style="text-align: center;">No hay productos con bajo stock</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($productos_sin_stock as $producto): ?>
                                            <tr>
                                                <td data-label="Producto"><?php echo $producto['nombre']; ?></td>
                                                <td data-label="Stock">0</td>
                                                <td data-label="Estado"><span class="badge badge-danger">Sin Stock</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        
                                        <?php foreach ($productos_bajo_stock as $producto): ?>
                                            <tr>
                                                <td data-label="Producto"><?php echo $producto['nombre']; ?></td>
                                                <td data-label="Stock"><?php echo $producto['stock']; ?></td>
                                                <td data-label="Estado"><span class="badge badge-warning">Bajo Stock</span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex-column">
                <div class="section">
                    <div class="section-header">
                        <h3 class="section-title">Productos Recientes</h3>
                        <a href="productos.php" class="section-link">Ver todos <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="card">
                        <div class="table-container">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Producto</th>
                                        <?php if ($columna_fecha_existe): ?>
                                        <th>Fecha</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($productos_recientes)): ?>
                                        <tr>
                                            <td colspan="2" style="text-align: center;">No hay productos recientes</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($productos_recientes as $producto): ?>
                                            <tr>
                                                <td data-label="Producto"><?php echo $producto['nombre']; ?></td>
                                                <?php if ($columna_fecha_existe): ?>
                                                <td data-label="Fecha">
                                                    <?php 
                                                    if (isset($producto['fecha_creacion'])) {
                                                        echo date('d/m/Y', strtotime($producto['fecha_creacion']));
                                                    } else {
                                                        echo 'N/A';
                                                    }
                                                    ?>
                                                </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header">
                <h3 class="section-title">Acciones Rápidas</h3>
            </div>
            
            <div class="dashboard-grid">
                <a href="agregar-producto.php" class="card" style="text-decoration: none; text-align: center; padding: 30px;">
                    <i class="fas fa-plus-circle" style="font-size: 36px; color: #945a42; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #333;">Agregar Producto</h3>
                    <p style="color: #666; margin: 0;">Añadir un nuevo producto al catálogo</p>
                </a>
                
                <a href="promociones.php" class="card" style="text-decoration: none; text-align: center; padding: 30px;">
                    <i class="fas fa-tag" style="font-size: 36px; color: #945a42; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #333;">Gestionar Promociones</h3>
                    <p style="color: #666; margin: 0;">Administrar promociones y descuentos</p>
                </a>
                
                <a href="gestionar-margenes.php" class="card" style="text-decoration: none; text-align: center; padding: 30px;">
                    <i class="fas fa-percentage" style="font-size: 36px; color: #945a42; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #333;">Gestionar Márgenes</h3>
                    <p style="color: #666; margin: 0;">Configurar márgenes de ganancia</p>
                </a>
                
                <?php if ($tabla_pedidos_existe): ?>
                <a href="ventas-dashboard.php" class="card" style="text-decoration: none; text-align: center; padding: 30px;">
                    <i class="fas fa-chart-line" style="font-size: 36px; color: #945a42; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #333;">Dashboard de Ventas</h3>
                    <p style="color: #666; margin: 0;">Ver estadísticas detalladas de ventas</p>
                </a>
                <?php endif; ?>
                
                <a href="importar-productos.php" class="card" style="text-decoration: none; text-align: center; padding: 30px;">
                    <i class="fas fa-file-import" style="font-size: 36px; color: #945a42; margin-bottom: 15px;"></i>
                    <h3 style="margin-bottom: 10px; color: #333;">Importar Productos</h3>
                    <p style="color: #666; margin: 0;">Importar productos desde CSV</p>
                </a>
            </div>
        </div>
    </div>
    
    <!-- Botón flotante para móvil -->
    <a href="agregar-producto.php" class="mobile-fab">
        <i class="fas fa-plus"></i>
    </a>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Convertir tablas a formato móvil
        function makeTablesResponsive() {
            // Obtener todas las tablas
            const tables = document.querySelectorAll('.table');
            
            tables.forEach(table => {
                // Obtener textos de encabezados
                const headerCells = table.querySelectorAll('thead th');
                const headerTexts = Array.from(headerCells).map(cell => cell.textContent.trim());
                
                // Agregar atributos data-label a celdas
                const bodyCells = table.querySelectorAll('tbody td');
                bodyCells.forEach((cell, index) => {
                    const rowIndex = Math.floor(index / headerTexts.length);
                    const columnIndex = index % headerTexts.length;
                    if (columnIndex < headerTexts.length) {
                        cell.setAttribute('data-label', headerTexts[columnIndex]);
                    }
                });
            });
        }
        
        // Ejecutar función
        makeTablesResponsive();
    });
    </script>
</body>
</html>