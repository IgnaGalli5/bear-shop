<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Obtener estadísticas básicas
$totalProductos = obtenerResultados("SELECT COUNT(*) as total FROM productos")[0]['total'];

// Obtener estadísticas de productos con/sin precio de costo
$estadisticasPrecios = obtenerResultados("
    SELECT 
        COUNT(CASE WHEN precio_costo IS NOT NULL THEN 1 END) as con_costo,
        COUNT(CASE WHEN precio_costo IS NULL THEN 1 END) as sin_costo
    FROM productos
")[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos básicos */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
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
        }
        .user-info span {
            margin-right: 15px;
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
        .dashboard-cards {
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
        .card-title {
            margin-top: 0;
            color: #945a42;
            font-size: 18px;
        }
        .card-value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        .card-subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        .main-actions {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        .main-actions .btn {
            padding: 15px 20px;
            font-size: 16px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100px;
        }
        .main-actions .btn i {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .section-title {
            color: #945a42;
            margin: 30px 0 15px 0;
            font-size: 24px;
            border-bottom: 2px solid #eec8a3;
            padding-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .main-actions {
                grid-template-columns: 1fr;
            }
            .dashboard-cards {
                grid-template-columns: 1fr;
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
                <span>Bienvenida Shamira, <?php echo $_SESSION['admin_nombre']; ?></span>
                <a href="logout.php" class="btn">Cerrar Sesión</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <h2 class="section-title">Resumen</h2>
        <div class="dashboard-cards">
            <div class="card">
                <h3 class="card-title">Total de Productos</h3>
                <div class="card-value"><?php echo $totalProductos; ?></div>
            </div>
            <div class="card">
                <h3 class="card-title">Productos con Precio de Costo</h3>
                <div class="card-value"><?php echo $estadisticasPrecios['con_costo']; ?></div>
                <p class="card-subtitle">Listos para aplicar márgenes</p>
            </div>
            <div class="card">
                <h3 class="card-title">Productos sin Precio de Costo</h3>
                <div class="card-value"><?php echo $estadisticasPrecios['sin_costo']; ?></div>
                <p class="card-subtitle">Requieren actualización</p>
            </div>
        </div>
        
        <h2 class="section-title">Gestión</h2>
        <div class="main-actions">
            <a href="productos.php" class="btn">
                <i class="fas fa-box"></i> 
                <span>Gestionar Productos</span>
            </a>
            <a href="gestionar-margenes.php" class="btn">
                <i class="fas fa-percentage"></i> 
                <span>Gestionar Márgenes</span>
            </a>
            <a href="newsletters.php" class="btn">
                <i class="fas fa-envelope"></i> 
                <span>Gestionar Newsletters</span>
            </a>
            <a href="promociones.php" class="btn">
                <i class="fas fa-tag"></i> 
                <span>Gestionar Promociones</span>
            </a>
        </div>
    </div>
</body>
</html>