<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Paginación
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$por_pagina = 50;
$offset = ($pagina - 1) * $por_pagina;

// Filtros
$filtro_tipo = isset($_GET['tipo']) ? escapar($_GET['tipo']) : '';
$filtro_fecha = isset($_GET['fecha']) ? escapar($_GET['fecha']) : '';

// Construir consulta
$sql_where = "";
$params = [];

if ($filtro_tipo) {
    $sql_where .= " AND h.tipo_accion = '$filtro_tipo'";
}

if ($filtro_fecha) {
    $sql_where .= " AND DATE(h.fecha) = '$filtro_fecha'";
}

// Obtener total de registros
$total_registros = obtenerResultados("
    SELECT COUNT(*) as total
    FROM historial_precios h
    WHERE 1=1 $sql_where
")[0]['total'];

$total_paginas = ceil($total_registros / $por_pagina);

// Obtener historial
$historial = obtenerResultados("
    SELECT h.*
    FROM historial_precios h
    WHERE 1=1 $sql_where
    ORDER BY h.fecha DESC
    LIMIT $offset, $por_pagina
");

// Obtener tipos de acciones para el filtro
$tipos_accion = obtenerResultados("
    SELECT DISTINCT tipo_accion
    FROM historial_precios
    ORDER BY tipo_accion
");

// Obtener fechas únicas para el filtro
$fechas = obtenerResultados("
    SELECT DISTINCT DATE(fecha) as fecha
    FROM historial_precios
    ORDER BY fecha DESC
    LIMIT 30
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Cambios de Precios - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos similares a gestionar-margenes.php */
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
        .btn-sm {
            padding: 5px 10px;
            font-size: 14px;
        }
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
        }
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
        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-group {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .filter-group label {
            font-weight: bold;
        }
        .filter-group select, .filter-group input {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .pagination {
            display: flex;
            justify-content: center;
            gap: 5px;
            margin-top: 20px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #945a42;
        }
        .pagination a:hover {
            background-color: #f5f5f5;
        }
        .pagination .active {
            background-color: #945a42;
            color: white;
            border-color: #945a42;
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
    </style>
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <h1>BEAR SHOP - ADMIN</h1>
            </div>
            <div class="user-info">
                <a href="gestionar-margenes.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Volver a Márgenes
                </a>
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Historial Completo de Cambios de Precios</h2>
            <p>Consulta todos los cambios realizados a los precios y márgenes de los productos.</p>
        </div>
        
        <div class="card">
            <form method="GET" action="">
                <div class="filters">
                    <div class="filter-group">
                        <label for="tipo">Tipo de Acción:</label>
                        <select name="tipo" id="tipo">
                            <option value="">Todos</option>
                            <?php foreach ($tipos_accion as $tipo): ?>
                                <option value="<?php echo $tipo['tipo_accion']; ?>" <?php echo $filtro_tipo == $tipo['tipo_accion'] ? 'selected' : ''; ?>>
                                    <?php 
                                        switch ($tipo['tipo_accion']) {
                                            case 'margen_global':
                                                echo 'Actualización de margen global';
                                                break;
                                            case 'margenes_categoria':
                                                echo 'Actualización de márgenes por categoría';
                                                break;
                                            case 'precio_individual':
                                                echo 'Actualización de precio individual';
                                                break;
                                            case 'recalculo_precios':
                                                echo 'Recálculo de todos los precios';
                                                break;
                                            default:
                                                echo $tipo['tipo_accion'];
                                        }
                                    ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="fecha">Fecha:</label>
                        <select name="fecha" id="fecha">
                            <option value="">Todas</option>
                            <?php foreach ($fechas as $fecha): ?>
                                <option value="<?php echo $fecha['fecha']; ?>" <?php echo $filtro_fecha == $fecha['fecha'] ? 'selected' : ''; ?>>
                                    <?php echo date('d/m/Y', strtotime($fecha['fecha'])); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    
                    <a href="historial-completo.php" class="btn">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </a>
                </div>
            </form>
            
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
                            <td colspan="4" class="text-center">No hay registros que coincidan con los filtros.</td>
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
                                    <button type="button" class="btn btn-sm ver-detalles" data-archivo="<?php echo $registro['archivo_log']; ?>">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <!-- Paginación -->
            <?php if ($total_paginas > 1): ?>
                <div class="pagination">
                    <?php if ($pagina > 1): ?>
                        <a href="?pagina=1<?php echo $filtro_tipo ? '&tipo=' . $filtro_tipo : ''; ?><?php echo $filtro_fecha ? '&fecha=' . $filtro_fecha : ''; ?>">
                            <i class="fas fa-angle-double-left"></i>
                        </a>
                        <a href="?pagina=<?php echo $pagina - 1; ?><?php echo $filtro_tipo ? '&tipo=' . $filtro_tipo : ''; ?><?php echo $filtro_fecha ? '&fecha=' . $filtro_fecha : ''; ?>">
                            <i class="fas fa-angle-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fin = min($total_paginas, $pagina + 2);
                    
                    for ($i = $inicio; $i <= $fin; $i++): ?>
                        <a href="?pagina=<?php echo $i; ?><?php echo $filtro_tipo ? '&tipo=' . $filtro_tipo : ''; ?><?php echo $filtro_fecha ? '&fecha=' . $filtro_fecha : ''; ?>" class="<?php echo $i == $pagina ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($pagina < $total_paginas): ?>
                        <a href="?pagina=<?php echo $pagina + 1; ?><?php echo $filtro_tipo ? '&tipo=' . $filtro_tipo : ''; ?><?php echo $filtro_fecha ? '&fecha=' . $filtro_fecha : ''; ?>">
                            <i class="fas fa-angle-right"></i>
                        </a>
                        <a href="?pagina=<?php echo $total_paginas; ?><?php echo $filtro_tipo ? '&tipo=' . $filtro_tipo : ''; ?><?php echo $filtro_fecha ? '&fecha=' . $filtro_fecha : ''; ?>">
                            <i class="fas fa-angle-double-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal para ver detalles de cambio -->
    <div id="modal_detalles" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h3>Detalles del Cambio</h3>
            
            <pre id="detalles_contenido" style="background-color: #f5f5f5; padding: 15px; border-radius: 4px; overflow: auto; max-height: 400px;"></pre>
            
            <button type="button" class="btn cerrar-modal">
                <i class="fas fa-times"></i> Cerrar
            </button>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Modal para ver detalles
            const modal = document.getElementById('modal_detalles');
            const botonesVerDetalles = document.querySelectorAll('.ver-detalles');
            const botonesCerrar = document.querySelectorAll('.close, .cerrar-modal');
            
            // Abrir modal de detalles
            botonesVerDetalles.forEach(function(boton) {
                boton.addEventListener('click', function() {
                    const archivo = this.getAttribute('data-archivo');
                    
                    // Cargar contenido del archivo
                    fetch('ver-log.php?archivo=' + encodeURIComponent(archivo))
                        .then(response => response.text())
                        .then(data => {
                            document.getElementById('detalles_contenido').textContent = data;
                            modal.style.display = 'block';
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
                });
            });
            
            // Cerrar modal al hacer clic fuera
            window.addEventListener('click', function(event) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>