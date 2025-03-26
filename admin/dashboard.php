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
        }
        .btn:hover {
            background-color: #e5b78e;
        }
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
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
        .main-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }
        .main-actions .btn {
            padding: 12px 20px;
            font-size: 16px;
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
        <div class="dashboard-cards">
            <div class="card">
                <h3 class="card-title">Total de Productos</h3>
                <div class="card-value"><?php echo $totalProductos; ?></div>
            </div>
        </div>
        
        
        <div class="main-actions">
            <a href="productos.php" class="btn">
                <i class="fas fa-box"></i> Gestionar Productos
            </a>
            <a href="newsletters.php" class="btn">
                <i class="fas fa-envelope"></i> Gestionar Newsletters
            </a>
            <a href="promociones.php" class="btn">
                <i class="fas fa-tag"></i> Gestionar Promociones
</a>    
</div>
    </div>
</body>
</html>