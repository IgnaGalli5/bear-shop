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
        }
        .btn-primary:hover {
            background-color: #7a4a37;
        }
        
        /* Tarjetas y contenedores */
        .page-header {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .page-title {
            color: #945a42;
            margin: 0;
            font-size: 28px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            margin-bottom: 15px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .card-title {
            color: #945a42;
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
        }
        
        /* Estadísticas */
        .stat-card {
            text-align: center;
            padding: 15px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            margin: 10px 0;
            color: #945a42;
        }
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        .stat-change {
            font-size: 14px;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
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
        
        /* Filtros */
        .filters {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filter-label {
            font-weight: bold;
            color: #333;
        }
        .filter-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .flex-row {
                flex-direction: column;
            }
            .stats-grid {
                grid-template-columns: 1fr;
            }
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            .filter-group {
                flex-direction: column;
                align-items: stretch;
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
            <form method="GET" id="filtro-form">
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
                <div class="stat-label">Total Ventas</div>
                <div class="stat-value">$<?php echo number_format($estadisticas_actuales['total_ventas'], 2, ',', '.'); ?></div>
                <div class="stat-change <?php echo $variacion_ventas >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $variacion_ventas >= 0 ? '+' : ''; ?><?php echo number_format($variacion_ventas, 2); ?>%
                </div>
            </div>
            
            <div class="card stat-card">
                <div class="stat-label">Total Pedidos</div>
                <div class="stat-value"><?php echo $estadisticas_actuales['total_pedidos']; ?></div>
                <div class="stat-change <?php echo $variacion_pedidos >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo $variacion_pedidos >= 0 ? '+' : ''; ?><?php echo number_format($variacion_pedidos, 2); ?>%
                </div>
            </div>
            
            <div class="card stat-card">
                <div class="stat-label">Promedio por Venta</div>
                <div class="stat-value">$<?php echo number_format($estadisticas_actuales['promedio_venta'], 2, ',', '.'); ?></div>
            </div>
            
            <div class="card stat-card">
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
        
        <!-- Productos más vendidos -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Productos Más Vendidos - <?php echo $titulo_periodo; ?></h3>
            </div>
            
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
    </div>
    
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
                            tension: 0.1
                        },
                        {
                            label: '<?php echo $titulo_comparacion; ?>',
                            data: <?php echo json_encode($datos_ventas_comparacion); ?>,
                            backgroundColor: 'rgba(238, 200, 163, 0.2)',
                            borderColor: 'rgba(238, 200, 163, 1)',
                            borderWidth: 2,
                            tension: 0.1
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
                            borderWidth: 1
                        },
                        {
                            label: '<?php echo $titulo_comparacion; ?>',
                            data: <?php echo json_encode($datos_pedidos_comparacion); ?>,
                            backgroundColor: 'rgba(238, 200, 163, 0.7)',
                            borderColor: 'rgba(238, 200, 163, 1)',
                            borderWidth: 1
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
                    }
                }
            });
        });
    </script>
</body>
</html>

