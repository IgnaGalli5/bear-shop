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
        
        /* Contenedores y secciones */
        .page-header {
            margin-bottom: 20px;
        }
        .page-title {
            color: #945a42;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        /* Formularios */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 10px;
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
        
        /* Mensajes */
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #c62828;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .error-message:before {
            content: '\f071';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 18px;
        }
        .success-message {
            background-color: #e8f5e9;
            color: #2e7d32;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message:before {
            content: '\f00c';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 18px;
        }
        
        /* Botón de envío */
        .submit-btn {
            background-color: #945a42;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            max-width: 300px;
            transition: background-color 0.2s ease, transform 0.2s ease;
        }
        .submit-btn:hover {
            background-color: #7a4a37;
            transform: translateY(-2px);
        }
        
        /* Plantilla CSV */
        .csv-template {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
            font-family: monospace;
            overflow-x: auto;
            border-left: 4px solid #945a42;
            font-size: 14px;
            line-height: 1.5;
        }
        
        /* Instrucciones */
        .instructions {
            margin-bottom: 20px;
        }
        .instructions h3 {
            color: #945a42;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .instructions ul {
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
        }
        
        /* Área de carga de archivos */
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            margin-bottom: 20px;
            transition: border-color 0.2s ease, background-color 0.2s ease;
            cursor: pointer;
        }
        .file-upload-area:hover {
            border-color: #945a42;
            background-color: rgba(148, 90, 66, 0.05);
        }
        .file-upload-area i {
            font-size: 48px;
            color: #945a42;
            margin-bottom: 15px;
        }
        .file-upload-area p {
            margin: 0;
            color: #666;
        }
        .file-upload-area input[type="file"] {
            display: none;
        }
        .file-name {
            margin-top: 10px;
            font-weight: bold;
            display: none;
        }
        
        /* Estilos responsivos */
        @media (max-width: 992px) {
            .container {
                max-width: 100%;
                padding: 15px;
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
            
            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }
            
            .form-container {
                padding: 15px;
            }
            
            .page-title {
                font-size: 24px;
                text-align: center;
            }
            
            .page-header p {
                text-align: center;
            }
            
            .csv-template {
                font-size: 12px;
                padding: 10px;
                white-space: nowrap;
            }
            
            .submit-btn {
                width: 100%;
                max-width: none;
            }
            
            .file-upload-area {
                padding: 20px;
            }
            
            .file-upload-area i {
                font-size: 36px;
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
            border: none;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
            .mobile-fab {
                display: flex;
            }
            
            .submit-btn {
                display: none;
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
            <div class="instructions">
                <h3>Instrucciones para importar productos</h3>
                <ul>
                    <li>Prepara un archivo CSV con las columnas requeridas (ver formato abajo).</li>
                    <li>Asegúrate de que los datos estén correctamente formateados.</li>
                    <li>Las columnas <strong>nombre</strong>, <strong>precio</strong> y <strong>categoria</strong> son obligatorias.</li>
                    <li>Para las imágenes, indica la ruta relativa (ej: "productos/imagen.jpg").</li>
                    <li>Para las características, separa cada una con un salto de línea (\n).</li>
                </ul>
            </div>
            
            <h3>Formato del archivo CSV</h3>
            <div class="csv-template">
                nombre,precio,cuotas,imagen,categoria,descripcion,caracteristicas,modo_uso,calificacion,num_calificaciones<br>
                "Producto 1",25000,3,"productos/producto1.jpg","skincare","Descripción 1","Característica 1\nCaracterística 2","Modo de uso 1",4.5,10<br>
                "Producto 2",30000,3,"productos/producto2.jpg","maquillaje","Descripción 2","Característica 1\nCaracterística 2","Modo de uso 2",4.7,15
            </div>
            
            <form method="POST" enctype="multipart/form-data" id="upload-form">
                <div class="file-upload-area" id="upload-area">
                    <i class="fas fa-file-csv"></i>
                    <p>Haz clic aquí para seleccionar un archivo CSV</p>
                    <p class="file-name" id="file-name"></p>
                    <input type="file" id="archivo_csv" name="archivo_csv" accept=".csv" required>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-upload"></i> Importar Productos
                </button>
            </form>
        </div>
    </div>
    
    <!-- Botón flotante para móvil -->
    <button type="button" class="mobile-fab" id="mobile-submit">
        <i class="fas fa-upload"></i>
    </button>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('upload-area');
            const fileInput = document.getElementById('archivo_csv');
            const fileName = document.getElementById('file-name');
            const mobileSubmitBtn = document.getElementById('mobile-submit');
            const form = document.getElementById('upload-form');
            
            // Hacer clic en el área de carga para activar el input de archivo
            uploadArea.addEventListener('click', function() {
                fileInput.click();
            });
            
            // Mostrar el nombre del archivo seleccionado
            fileInput.addEventListener('change', function() {
                if (this.files && this.files[0]) {
                    fileName.textContent = 'Archivo seleccionado: ' + this.files[0].name;
                    fileName.style.display = 'block';
                    uploadArea.style.borderColor = '#945a42';
                } else {
                    fileName.style.display = 'none';
                    uploadArea.style.borderColor = '#ddd';
                }
            });
            
            // Evento para el botón flotante en móvil
            mobileSubmitBtn.addEventListener('click', function() {
                // Verificar si hay un archivo seleccionado
                if (fileInput.files && fileInput.files[0]) {
                    form.submit();
                } else {
                    alert('Por favor, selecciona un archivo CSV primero.');
                    fileInput.click();
                }
            });
            
            // Prevenir el comportamiento por defecto del drag and drop
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Resaltar el área de carga cuando se arrastra un archivo sobre ella
            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, highlight, false);
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, unhighlight, false);
            });
            
            function highlight() {
                uploadArea.style.borderColor = '#945a42';
                uploadArea.style.backgroundColor = 'rgba(148, 90, 66, 0.05)';
            }
            
            function unhighlight() {
                uploadArea.style.borderColor = '#ddd';
                uploadArea.style.backgroundColor = '';
            }
            
            // Manejar el drop de archivos
            uploadArea.addEventListener('drop', handleDrop, false);
            
            function handleDrop(e) {
                const dt = e.dataTransfer;
                const files = dt.files;
                
                if (files && files.length) {
                    fileInput.files = files;
                    
                    // Disparar el evento change manualmente
                    const event = new Event('change');
                    fileInput.dispatchEvent(event);
                }
            }
        });
    </script>
</body>
</html>