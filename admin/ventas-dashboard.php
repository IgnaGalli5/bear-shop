<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Obtener período de tiempo seleccionado (por defecto: mes actual)
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'mes';
$anio_comparacion = isset($_GET['anio_comparacion']) ? (int)$_GET['anio_comparacion'] : date('Y') - 1;
$anio_actual = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$mes_actual = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');

// Función para obtener datos de ventas por período
function obtenerVentasPorPeriodo($periodo, $anio, $mes = null) {
    $where_clause = "";
    
    switch ($periodo) {
        case 'mes':
            $where_clause = "YEAR(fecha) = $anio AND MONTH(fecha) = $mes";
            $group_by = "DAY(fecha)";
            $select_date = "DATE(fecha) as fecha_agrupada";
            $format_date = "d/m/Y";
            break;
        case 'semestre':
            $semestre = $mes <= 6 ? 1 : 2;
            $inicio_semestre = $semestre == 1 ? 1 : 7;
            $fin_semestre = $semestre == 1 ? 6 : 12;
            $where_clause = "YEAR(fecha) = $anio AND MONTH(fecha) BETWEEN $inicio_semestre AND $fin_semestre";
            $group_by = "MONTH(fecha)";
            $select_date = "CONCAT(YEAR(fecha), '-', MONTH(fecha), '-01') as fecha_agrupada";
            $format_date = "m/Y";
            break;
        case 'anio':
            $where_clause = "YEAR(fecha) = $anio";
            $group_by = "MONTH(fecha)";
            $select_date = "CONCAT(YEAR(fecha), '-', MONTH(fecha), '-01') as fecha_agrupada";
            $format_date = "m/Y";
            break;
        default:
            $where_clause = "YEAR(fecha) = $anio AND MONTH(fecha) = $mes";
            $group_by = "DAY(fecha)";
            $select_date = "DATE(fecha) as fecha_agrupada";
            $format_date = "d/m/Y";
    }
    
    // Consulta para obtener ventas agrupadas por período
    $sql = "
        SELECT 
            $select_date,
            COUNT(id) as total_pedidos,
            SUM(total) as total_ventas,
            AVG(total) as promedio_venta
        FROM 
            pedidos
        WHERE 
            $where_clause AND estado = 'completado'
        GROUP BY 
            $group_by
        ORDER BY 
            fecha_agrupada
    ";
    
    $resultados = obtenerResultados($sql);
    
    // Formatear fechas para mostrar
    foreach ($resultados as &$resultado) {
        $fecha = new DateTime($resultado['fecha_agrupada']);
        $resultado['fecha_formateada'] = $fecha->format($format_date);
    }
    
    return $resultados;
}

// Función para obtener productos más vendidos
function obtenerProductosMasVendidos($periodo, $anio, $mes = null, $limite = 10) {
    $where_clause = "";
    
    switch ($periodo) {
        case 'mes':
            $where_clause = "YEAR(p.fecha) = $anio AND MONTH(p.fecha) = $mes";
            break;
        case 'semestre':
            $semestre = $mes <= 6 ? 1 : 2;
            $inicio_semestre = $semestre == 1 ? 1 : 7;
            $fin_semestre = $semestre == 1 ? 6 : 12;
            $where_clause = "YEAR(p.fecha) = $anio AND MONTH(p.fecha) BETWEEN $inicio_semestre AND $fin_semestre";
            break;
        case 'anio':
            $where_clause = "YEAR(p.fecha) = $anio";
            break;
        default:
            $where_clause = "YEAR(p.fecha) = $anio AND MONTH(p.fecha) = $mes";
    }
    
    $sql = "
        SELECT 
            pr.id,
            pr.nombre,
            pr.categoria,
            SUM(pi.cantidad) as total_vendido,
            SUM(pi.subtotal) as total_ingresos
        FROM 
            pedido_items pi
        JOIN 
            pedidos p ON pi.pedido_id = p.id
        JOIN 
            productos pr ON pi.producto_id = pr.id
        WHERE 
            $where_clause AND p.estado = 'completado'
        GROUP BY 
            pr.id
        ORDER BY 
            total_vendido DESC
        LIMIT $limite
    ";
    
    return obtenerResultados($sql);
}

// Función para obtener estadísticas generales
function obtenerEstadisticasGenerales($periodo, $anio, $mes = null) {
    $where_clause = "";
    
    switch ($periodo) {
        case 'mes':
            $where_clause = "YEAR(fecha) = $anio AND MONTH(fecha) = $mes";
            break;
        case 'semestre':
            $semestre = $mes <= 6 ? 1 : 2;
            $inicio_semestre = $semestre == 1 ? 1 : 7;
            $fin_semestre = $semestre == 1 ? 6 : 12;
            $where_clause = "YEAR(fecha) = $anio AND MONTH(fecha) BETWEEN $inicio_semestre AND $fin_semestre";
            break;
        case 'anio':
            $where_clause = "YEAR(fecha) = $anio";
            break;
        default:
            $where_clause = "YEAR(fecha) = $anio AND MONTH(fecha) = $mes";
    }
    
    $sql = "
        SELECT 
            COUNT(id) as total_pedidos,
            SUM(total) as total_ventas,
            AVG(total) as promedio_venta,
            MIN(total) as venta_minima,
            MAX(total) as venta_maxima
        FROM 
            pedidos
        WHERE 
            $where_clause AND estado = 'completado'
    ";
    
    $resultados = obtenerResultados($sql);
    
    if (!empty($resultados)) {
        return $resultados[0];
    } else {
        return [
            'total_pedidos' => 0,
            'total_ventas' => 0,
            'promedio_venta' => 0,
            'venta_minima' => 0,
            'venta_maxima' => 0
        ];
    }
}

// Obtener datos para el período actual
$ventas_periodo_actual = obtenerVentasPorPeriodo($periodo, $anio_actual, $mes_actual);
$productos_mas_vendidos = obtenerProductosMasVendidos($periodo, $anio_actual, $mes_actual);
$estadisticas_actuales = obtenerEstadisticasGenerales($periodo, $anio_actual, $mes_actual);

// Obtener datos para el período de comparación
$ventas_periodo_comparacion = obtenerVentasPorPeriodo($periodo, $anio_comparacion, $mes_actual);
$estadisticas_comparacion = obtenerEstadisticasGenerales($periodo, $anio_comparacion, $mes_actual);

// Calcular variación porcentual
$variacion_ventas = 0;
if ($estadisticas_comparacion['total_ventas'] > 0) {
    $variacion_ventas = (($estadisticas_actuales['total_ventas'] - $estadisticas_comparacion['total_ventas']) / $estadisticas_comparacion['total_ventas']) * 100;
}

$variacion_pedidos = 0;
if ($estadisticas_comparacion['total_pedidos'] > 0) {
    $variacion_pedidos = (($estadisticas_actuales['total_pedidos'] - $estadisticas_comparacion['total_pedidos']) / $estadisticas_comparacion['total_pedidos']) * 100;
}

// Preparar datos para gráficos
$labels_ventas = [];
$datos_ventas_actual = [];
$datos_pedidos_actual = [];

foreach ($ventas_periodo_actual as $venta) {
    $labels_ventas[] = $venta['fecha_formateada'];
    $datos_ventas_actual[] = $venta['total_ventas'];
    $datos_pedidos_actual[] = $venta['total_pedidos'];
}

$datos_ventas_comparacion = [];
$datos_pedidos_comparacion = [];

// Asegurar que los datos de comparación tengan la misma longitud que los actuales
foreach ($labels_ventas as $index => $label) {
    $datos_ventas_comparacion[$index] = 0;
    $datos_pedidos_comparacion[$index] = 0;
}

foreach ($ventas_periodo_comparacion as $venta) {
    // Buscar el índice correspondiente en el período actual
    $fecha_formateada = $venta['fecha_formateada'];
    $indice = array_search($fecha_formateada, $labels_ventas);
    
    if ($indice !== false) {
        $datos_ventas_comparacion[$indice] = $venta['total_ventas'];
        $datos_pedidos_comparacion[$indice] = $venta['total_pedidos'];
    }
}

// Obtener nombres de meses para selector
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

// Título del período
$titulo_periodo = "";
switch ($periodo) {
    case 'mes':
        $titulo_periodo = $nombres_meses[$mes_actual] . " " . $anio_actual;
        break;
    case 'semestre':
        $semestre = $mes_actual <= 6 ? 1 : 2;
        $titulo_periodo = $semestre == 1 ? "Primer semestre " : "Segundo semestre ";
        $titulo_periodo .= $anio_actual;
        break;
    case 'anio':
        $titulo_periodo = "Año " . $anio_actual;
        break;
}

// Título del período de comparación
$titulo_comparacion = "";
switch ($periodo) {
    case 'mes':
        $titulo_comparacion = $nombres_meses[$mes_actual] . " " . $anio_comparacion;
        break;
    case 'semestre':
        $semestre = $mes_actual <= 6 ? 1 : 2;
        $titulo_comparacion = $semestre == 1 ? "Primer semestre " : "Segundo semestre ";
        $titulo_comparacion .= $anio_comparacion;
        break;
    case 'anio':
        $titulo_comparacion = "Año " . $anio_comparacion;
        break;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Ventas - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Variables CSS */
        :root {
            --primary: #945a42;
            --primary-dark: #7a4a37;
            --primary-light: #eec8a3;
            --secondary: #e5b78e;
            --success: #4CAF50;
            --success-dark: #388E3C;
            --danger: #f44336;
            --danger-dark: #d32f2f;
            --warning: #FF9800;
            --warning-dark: #F57C00;
            --info: #2196F3;
            --info-dark: #1976D2;
            --white: #ffffff;
            --light-gray: #f5f5f5;
            --gray: #eee;
            --dark-gray: #666;
            --black: #333;
            --shadow: rgba(0,0,0,0.1);
            --shadow-dark: rgba(0,0,0,0.2);
            --transition: all 0.3s ease;
        }
        
        /* Estilos base */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--light-gray);
            color: var(--black);
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        header {
            background-color: var(--primary);
            color: var(--white);
            padding: 15px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px var(--shadow);
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
        
        /* Botones */
        .btn {
            background-color: var(--primary-light);
            color: var(--primary);
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            transition: var(--transition);
        }
        
        .btn:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 2px 5px var(--shadow);
        }
        
        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
        }
        
        /* Encabezado de página */
        .page-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .page-title {
            color: var(--primary);
            margin: 0;
            font-size: 28px;
        }
        
        /* Tarjetas y contenedores */
        .card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow);
            padding: 20px;
            margin-bottom: 20px;
            transition: var(--transition);
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px var(--shadow-dark);
        }
        
        .card-header {
            margin-bottom: 15px;
            border-bottom: 1px solid var(--gray);
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .card-title {
            color: var(--primary);
            margin: 0;
            font-size: 18px;
        }
        
        /* Grid y flexbox */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-bottom: 20px;
        }
        
        .flex-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .flex-column {
            flex: 1;
            min-width: 0; /* Para evitar desbordamiento */
        }
        
        /* Estadísticas */
        .stat-card {
            text-align: center;
            padding: 15px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 150px;
        }
        
        .stat-icon {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 10px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: var(--primary);
        }
        
        .stat-label {
            color: var(--dark-gray);
            font-size: 14px;
            margin-bottom: 5px;
        }
        
        .stat-change {
            font-size: 14px;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
        }
        
        .stat-change.positive {
            background-color: #e8f5e9;
            color: #2e7d32;
        }
        
        .stat-change.negative {
            background-color: #ffebee;
            color: #c62828;
        }
        
        /* Tablas */
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        .table th, .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }
        
        .table th {
            background-color: var(--primary);
            color: var(--white);
            font-weight: normal;
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        .table tr:hover {
            background-color: var(--light-gray);
        }
        
        /* Filtros */
        .filters {
            background-color: var(--white);
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px var(--shadow);
        }
        
        .filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            flex: 1;
            min-width: 150px;
        }
        
        .filter-label {
            font-weight: bold;
            color: var(--black);
            font-size: 14px;
        }
        
        .filter-select {
            padding: 10px;
            border: 1px solid var(--gray);
            border-radius: 4px;
            background-color: var(--white);
            color: var(--black);
            font-size: 14px;
            transition: var(--transition);
        }
        
        .filter-select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(148, 90, 66, 0.2);
        }
        
        /* Tarjetas para móvil */
        .product-cards {
            display: none;
            flex-direction: column;
            gap: 15px;
        }
        
        .product-card {
            background-color: var(--white);
            border-radius: 8px;
            box-shadow: 0 2px 10px var(--shadow);
            padding: 15px;
            transition: var(--transition);
        }
        
        .product-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px var(--shadow-dark);
        }
        
        .product-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            border-bottom: 1px solid var(--gray);
            padding-bottom: 10px;
        }
        
        .product-card-title {
            font-size: 16px;
            font-weight: bold;
            color: var(--primary);
        }
        
        .product-card-body {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .product-card-item {
            display: flex;
            flex-direction: column;
        }
        
        .product-card-label {
            font-size: 12px;
            color: var(--dark-gray);
            margin-bottom: 2px;
        }
        
        .product-card-value {
            font-weight: bold;
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
            background-color: var(--primary);
            color: var(--white);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
            z-index: 999;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            text-decoration: none;
            border: none;
            transition: var(--transition);
        }
        
        .mobile-fab:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        
        /* Estilos responsivos */
        @media (max-width: 992px) {
            .container {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
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
                gap: 8px;
            }
            
            .page-title {
                font-size: 24px;
                width: 100%;
                text-align: center;
            }
            
            .flex-row {
                flex-direction: column;
                gap: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
            
            .table-container {
                display: none;
            }
            
            .product-cards {
                display: flex;
            }
            
            .mobile-fab {
                display: flex;
            }
            
            .chart-container {
                height: 250px;
            }
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 15px;
            }
            
            .card-title {
                font-size: 16px;
            }
            
            .stat-value {
                font-size: 20px;
            }
            
            .product-card-body {
                grid-template-columns: 1fr;
            }
            
            .chart-container {
                height: 200px;
            }
        }
        
        /* Tema oscuro */
        @media (prefers-color-scheme: dark) {
            :root {
                --white: #1e1e1e;
                --light-gray: #121212;
                --gray: #2c2c2c;
                --dark-gray: #999;
                --black: #e0e0e0;
                --shadow: rgba(0,0,0,0.3);
                --shadow-dark: rgba(0,0,0,0.5);
            }
            
            .table th {
                background-color: var(--primary-dark);
            }
            
            .stat-change.positive {
                background-color: rgba(46, 125, 50, 0.2);
            }
            
            .stat-change.negative {
                background-color: rgba(198, 40, 40, 0.2);
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
                <a href="dashboard.php" class="btn">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="productos.php" class="btn">
                    <i class="fas fa-box"></i> Productos
                </a>
                <a href="promociones.php" class="btn">
                    <i class="fas fa-tag"></i> Promociones
                </a>
                <a href="logout.php" class="btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Dashboard de Ventas</h2>
        </div>
        
        <!-- Filtros -->
        <div class="filters">
            <form method="GET" id="filtro-form" class="filter-form">
                <div class="filter-group">
                    <label class="filter-label">Período:</label>
                    <select name="periodo" class="filter-select" onchange="this.form.submit()">
                        <option value="mes" <?php echo $periodo == 'mes' ? 'selected' : ''; ?>>Mensual</option>
                        <option value="semestre" <?php echo $periodo == 'semestre' ? 'selected' : ''; ?>>Semestral</option>
                        <option value="anio" <?php echo $periodo == 'anio' ? 'selected' : ''; ?>>Anual</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label class="filter-label">Año:</label>
                    <select name="anio" class="filter-select" onchange="this.form.submit()">
                        <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $anio_actual == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <?php if ($periodo == 'mes'): ?>
                <div class="filter-group">
                    <label class="filter-label">Mes:</label>
                    <select name="mes" class="filter-select" onchange="this.form.submit()">
                        <?php foreach ($nombres_meses as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>" <?php echo $mes_actual == $num ? 'selected' : ''; ?>><?php echo $nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                
                <div class="filter-group">
                    <label class="filter-label">Comparar con año:</label>
                    <select name="anio_comparacion" class="filter-select" onchange="this.form.submit()">
                        <?php for ($i = date('Y') - 1; $i >= date('Y') - 6; $i--): ?>
                            <option value="<?php echo $i; ?>" <?php echo $anio_comparacion == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </form>
        </div>
        
        <!-- Estadísticas generales -->
        <div class="stats-grid">
            <div class="card stat-card">
                <div class="stat-icon"><i class="fas fa-dollar-sign"></i></div>
                <div class="stat-label">Total Ventas</div>
                <div class="stat-value">$<?php echo number_format($estadisticas_actuales['total_ventas'], 2, ',', '.'); ?></div>
                <div class="stat-change <?php echo $variacion_ventas >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas <?php echo $variacion_ventas >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                    <?php echo $variacion_ventas >= 0 ? '+' : ''; ?><?php echo number_format($variacion_ventas, 2); ?>%
                </div>
            </div>
            
            <div class="card stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-label">Total Pedidos</div>
                <div class="stat-value"><?php echo $estadisticas_actuales['total_pedidos']; ?></div>
                <div class="stat-change <?php echo $variacion_pedidos >= 0 ? 'positive' : 'negative'; ?>">
                    <i class="fas <?php echo $variacion_pedidos >= 0 ? 'fa-arrow-up' : 'fa-arrow-down'; ?>"></i>
                    <?php echo $variacion_pedidos >= 0 ? '+' : ''; ?><?php echo number_format($variacion_pedidos, 2); ?>%
                </div>
            </div>
            
            <div class="card stat-card">
                <div class="stat-icon"><i class="fas fa-calculator"></i></div>
                <div class="stat-label">Promedio por Venta</div>
                <div class="stat-value">$<?php echo number_format($estadisticas_actuales['promedio_venta'], 2, ',', '.'); ?></div>
            </div>
            
            <div class="card stat-card">
                <div class="stat-icon"><i class="fas fa-trophy"></i></div>
                <div class="stat-label">Venta Máxima</div>
                <div class="stat-value">$<?php echo number_format($estadisticas_actuales['venta_maxima'], 2, ',', '.'); ?></div>
            </div>
        </div>
        
        <!-- Gráficos -->
        <div class="flex-row">
            <div class="flex-column">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Ventas: <?php echo $titulo_periodo; ?> vs <?php echo $titulo_comparacion; ?></h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
            
            <div class="flex-column">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pedidos: <?php echo $titulo_periodo; ?> vs <?php echo $titulo_comparacion; ?></h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="pedidosChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Productos más vendidos - Vista de escritorio -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Productos Más Vendidos - <?php echo $titulo_periodo; ?></h3>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Unidades Vendidas</th>
                            <th>Total Ingresos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos_mas_vendidos)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center;">No hay datos de ventas para este período</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_mas_vendidos as $producto): ?>
                                <tr>
                                    <td><?php echo $producto['nombre']; ?></td>
                                    <td style="text-transform: capitalize;"><?php echo $producto['categoria']; ?></td>
                                    <td><?php echo $producto['total_vendido']; ?></td>
                                    <td>$<?php echo number_format($producto['total_ingresos'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Productos más vendidos - Vista móvil -->
            <div class="product-cards">
                <?php if (empty($productos_mas_vendidos)): ?>
                    <div class="product-card" style="text-align: center; padding: 30px 15px;">
                        <i class="fas fa-box" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                        <p>No hay datos de ventas para este período</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($productos_mas_vendidos as $producto): ?>
                        <div class="product-card">
                            <div class="product-card-header">
                                <div class="product-card-title"><?php echo $producto['nombre']; ?></div>
                            </div>
                            
                            <div class="product-card-body">
                                <div class="product-card-item">
                                    <div class="product-card-label">Categoría</div>
                                    <div class="product-card-value" style="text-transform: capitalize;"><?php echo $producto['categoria']; ?></div>
                                </div>
                                
                                <div class="product-card-item">
                                    <div class="product-card-label">Unidades Vendidas</div>
                                    <div class="product-card-value"><?php echo $producto['total_vendido']; ?></div>
                                </div>
                                
                                <div class="product-card-item">
                                    <div class="product-card-label">Total Ingresos</div>
                                    <div class="product-card-value">$<?php echo number_format($producto['total_ingresos'], 2, ',', '.'); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Botón flotante para móvil -->
    <button type="button" class="mobile-fab" id="mobile-filter-btn">
        <i class="fas fa-filter"></i>
    </button>
    
    <script>
        // Configuración de gráficos
        document.addEventListener('DOMContentLoaded', function() {
            // Gráfico de ventas
            const ventasCtx = document.getElementById('ventasChart').getContext('2d');
            const ventasChart = new Chart(ventasCtx, {
                type: 'line',
                data: {
                    labels: <?php echo json_encode($labels_ventas); ?>,
                    datasets: [
                        {
                            label: '<?php echo $titulo_periodo; ?>',
                            data: <?php echo json_encode($datos_ventas_actual); ?>,
                            backgroundColor: 'rgba(148, 90, 66, 0.2)',
                            borderColor: 'rgba(148, 90, 66, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            pointBackgroundColor: 'rgba(148, 90, 66, 1)',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        },
                        {
                            label: '<?php echo $titulo_comparacion; ?>',
                            data: <?php echo json_encode($datos_ventas_comparacion); ?>,
                            backgroundColor: 'rgba(238, 200, 163, 0.2)',
                            borderColor: 'rgba(238, 200, 163, 1)',
                            borderWidth: 2,
                            tension: 0.1,
                            pointBackgroundColor: 'rgba(238, 200, 163, 1)',
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': $' + context.raw.toLocaleString();
                                }
                            }
                        },
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: 'circle'
                            }
                        }
                    }
                }
            });
            
            // Gráfico de pedidos
            const pedidosCtx = document.getElementById('pedidosChart').getContext('2d');
            const pedidosChart = new Chart(pedidosCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($labels_ventas); ?>,
                    datasets: [
                        {
                            label: '<?php echo $titulo_periodo; ?>',
                            data: <?php echo json_encode($datos_pedidos_actual); ?>,
                            backgroundColor: 'rgba(148, 90, 66, 0.7)',
                            borderColor: 'rgba(148, 90, 66, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        },
                        {
                            label: '<?php echo $titulo_comparacion; ?>',
                            data: <?php echo json_encode($datos_pedidos_comparacion); ?>,
                            backgroundColor: 'rgba(238, 200, 163, 0.7)',
                            borderColor: 'rgba(238, 200, 163, 1)',
                            borderWidth: 1,
                            borderRadius: 4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12,
                                usePointStyle: true,
                                pointStyle: 'rect'
                            }
                        }
                    }
                }
            });
            
            // Botón flotante para móvil
            const mobileFilterBtn = document.getElementById('mobile-filter-btn');
            const filterForm = document.getElementById('filtro-form');
            
            mobileFilterBtn.addEventListener('click', function() {
                const filtersElement = document.querySelector('.filters');
                filtersElement.scrollIntoView({ behavior: 'smooth' });
            });
            
            // Ajustar gráficos al cambiar el tamaño de la ventana
            window.addEventListener('resize', function() {
                ventasChart.resize();
                pedidosChart.resize();
            });
            
            // Detectar tema oscuro
            const darkModeMediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
            const updateChartTheme = (isDarkMode) => {
                const textColor = isDarkMode ? '#e0e0e0' : '#333';
                const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
                
                Chart.defaults.color = textColor;
                Chart.defaults.borderColor = gridColor;
                
                ventasChart.options.scales.x.grid.color = gridColor;
                ventasChart.options.scales.y.grid.color = gridColor;
                pedidosChart.options.scales.x.grid.color = gridColor;
                pedidosChart.options.scales.y.grid.color = gridColor;
                
                ventasChart.update();
                pedidosChart.update();
            };
            
            updateChartTheme(darkModeMediaQuery.matches);
            darkModeMediaQuery.addEventListener('change', (e) => {
                updateChartTheme(e.matches);
            });
        });
    </script>
</body>
</html>