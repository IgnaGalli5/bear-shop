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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['crear_columnas'])) {
        try {
            // Añadir columna stock si no existe
            $resultado_stock = query("SHOW COLUMNS FROM productos LIKE 'stock'");
            if ($resultado_stock->num_rows === 0) {
                query("ALTER TABLE productos ADD COLUMN stock INT DEFAULT 0");
                $mensaje .= "Columna 'stock' añadida correctamente.<br>";
            } else {
                $mensaje .= "La columna 'stock' ya existe.<br>";
            }
            
            // Añadir columna fecha_creacion si no existe
            $resultado_fecha = query("SHOW COLUMNS FROM productos LIKE 'fecha_creacion'");
            if ($resultado_fecha->num_rows === 0) {
                query("ALTER TABLE productos ADD COLUMN fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
                $mensaje .= "Columna 'fecha_creacion' añadida correctamente.<br>";
            } else {
                $mensaje .= "La columna 'fecha_creacion' ya existe.<br>";
            }
            
        } catch (Exception $e) {
            $error = "Error al crear las columnas: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['crear_tablas'])) {
        try {
            // Verificar si la tabla pedidos ya existe
            $resultado_pedidos = query("SHOW TABLES LIKE 'pedidos'");
            if ($resultado_pedidos->num_rows === 0) {
                // Crear tabla pedidos
                query("
                    CREATE TABLE pedidos (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        cliente_id INT,
                        fecha DATETIME NOT NULL,
                        estado VARCHAR(50) NOT NULL,
                        total DECIMAL(10,2) NOT NULL,
                        metodo_pago VARCHAR(50),
                        direccion_envio TEXT,
                        notas TEXT,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    )
                ");
                $mensaje .= "Tabla 'pedidos' creada correctamente.<br>";
            } else {
                $mensaje .= "La tabla 'pedidos' ya existe.<br>";
            }
            
            // Verificar si la tabla pedido_items ya existe
            $resultado_items = query("SHOW TABLES LIKE 'pedido_items'");
            if ($resultado_items->num_rows === 0) {
                // Crear tabla pedido_items
                query("
                    CREATE TABLE pedido_items (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        pedido_id INT NOT NULL,
                        producto_id INT NOT NULL,
                        cantidad INT NOT NULL,
                        precio_unitario DECIMAL(10,2) NOT NULL,
                        subtotal DECIMAL(10,2) NOT NULL,
                        FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
                        FOREIGN KEY (producto_id) REFERENCES productos(id)
                    )
                ");
                $mensaje .= "Tabla 'pedido_items' creada correctamente.<br>";
            } else {
                $mensaje .= "La tabla 'pedido_items' ya existe.<br>";
            }
            
        } catch (Exception $e) {
            $error = "Error al crear las tablas: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurar Base de Datos - Bear Shop</title>
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
            max-width: 800px;
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
            max-width: 800px;
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
            margin: 0 0 20px 0;
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
        
        /* Secciones */
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            color: #945a42;
            margin: 0 0 15px 0;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
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
                <a href="logout.php" class="btn">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                </a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Configurar Base de Datos</h2>
            <p>Esta página te permite configurar la base de datos para el sistema de ventas.</p>
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
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Paso 1: Añadir Columnas a la Tabla Productos</h3>
                <p class="card-description">
                    Este paso añadirá las columnas 'stock' y 'fecha_creacion' a la tabla productos si no existen.
                </p>
            </div>
            
            <form method="POST">
                <button type="submit" name="crear_columnas" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Añadir Columnas
                </button>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Paso 2: Crear Tablas para el Sistema de Ventas</h3>
                <p class="card-description">
                    Este paso creará las tablas 'pedidos' y 'pedido_items' necesarias para el sistema de ventas.
                </p>
            </div>
            
            <form method="POST">
                <button type="submit" name="crear_tablas" class="btn btn-primary">
                    <i class="fas fa-table"></i> Crear Tablas
                </button>
            </form>
        </div>
        
        <div class="section">
            <h3 class="section-title">¿Qué sigue?</h3>
            <p>
                Una vez que hayas configurado la base de datos, podrás acceder al dashboard de ventas y comenzar a registrar pedidos.
            </p>
            <p>
                <a href="dashboard.php" class="btn">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </p>
        </div>
    </div>
</body>
</html>

