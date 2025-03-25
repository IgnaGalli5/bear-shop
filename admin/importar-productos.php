<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$exito = '';
$productos_importados = 0;

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_csv'])) {
    $archivo = $_FILES['archivo_csv'];
    
    // Verificar que sea un archivo CSV
    $tipo = pathinfo($archivo['name'], PATHINFO_EXTENSION);
    if ($tipo != 'csv') {
        $error = 'El archivo debe ser CSV';
    } else {
        // Abrir el archivo
        $handle = fopen($archivo['tmp_name'], 'r');
        
        // Leer la primera línea (encabezados)
        $encabezados = fgetcsv($handle, 1000, ',');
        
        // Verificar que los encabezados sean correctos
        $encabezados_requeridos = ['nombre', 'precio', 'cuotas', 'imagen', 'categoria', 'descripcion', 'caracteristicas', 'modo_uso', 'calificacion', 'num_calificaciones'];
        $encabezados_validos = true;
        
        foreach ($encabezados_requeridos as $encabezado) {
            if (!in_array($encabezado, $encabezados)) {
                $encabezados_validos = false;
                $error = "El archivo CSV no tiene el formato correcto. Falta la columna '$encabezado'.";
                break;
            }
        }
        
        if ($encabezados_validos) {
            // Leer datos
            while (($datos = fgetcsv($handle, 1000, ',')) !== FALSE) {
                // Crear un array asociativo con los datos
                $producto = [];
                foreach ($encabezados as $index => $encabezado) {
                    $producto[$encabezado] = isset($datos[$index]) ? $datos[$index] : '';
                }
                
                // Validar datos mínimos
                if (empty($producto['nombre']) || empty($producto['precio']) || empty($producto['categoria'])) {
                    continue; // Saltar esta fila si faltan datos esenciales
                }
                
                // Asignar valores
                $nombre = escapar($producto['nombre']);
                $precio = (float)$producto['precio'];
                $cuotas = (int)($producto['cuotas'] ?: 3);
                $precio_cuota = $precio / $cuotas;
                $imagen = escapar($producto['imagen'] ?: 'productos/default.jpg');
                $categoria = escapar($producto['categoria']);
                $descripcion = escapar($producto['descripcion']);
                $caracteristicas = escapar($producto['caracteristicas']);
                $modo_uso = escapar($producto['modo_uso']);
                $calificacion = (float)($producto['calificacion'] ?: 5.0);
                $num_calificaciones = (int)($producto['num_calificaciones'] ?: 0);
                
                // Insertar en la base de datos
                $sql = "INSERT INTO productos (nombre, precio, cuotas, precio_cuota, imagen, categoria, descripcion, caracteristicas, modo_uso, calificacion, num_calificaciones) 
                        VALUES ('$nombre', $precio, $cuotas, $precio_cuota, '$imagen', '$categoria', '$descripcion', '$caracteristicas', '$modo_uso', $calificacion, $num_calificaciones)";
                
                if (query($sql)) {
                    $productos_importados++;
                }
            }
            
            if ($productos_importados > 0) {
                $exito = "Se importaron $productos_importados productos correctamente.";
            } else {
                $error = "No se pudo importar ningún producto. Verifica el formato del archivo.";
            }
        }
        
        fclose($handle);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importar Productos - Bear Shop</title>
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
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 10px;
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
        .csv-template {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-family: monospace;
            overflow-x: auto;
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
            <h2 class="page-title">Importar Productos desde CSV</h2>
            <p>Sube un archivo CSV con todos los productos que deseas importar.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="success-message">
                <?php echo $exito; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <h3>Formato del archivo CSV</h3>
            <div class="csv-template">
                nombre,precio,cuotas,imagen,categoria,descripcion,caracteristicas,modo_uso,calificacion,num_calificaciones<br>
                "Producto 1",25000,3,"productos/producto1.jpg","skincare","Descripción 1","Característica 1\nCaracterística 2","Modo de uso 1",4.5,10<br>
                "Producto 2",30000,3,"productos/producto2.jpg","maquillaje","Descripción 2","Característica 1\nCaracterística 2","Modo de uso 2",4.7,15
            </div>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="archivo_csv">Seleccionar archivo CSV</label>
                    <input type="file" id="archivo_csv" name="archivo_csv" accept=".csv" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-upload"></i> Importar Productos
                </button>
            </form>
        </div>
    </div>
</body>
</html>