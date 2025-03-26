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
    if (isset($_POST['actualizar_margenes'])) {
        // Actualizar márgenes por categoría
        foreach ($_POST['multiplicador'] as $categoria => $multiplicador) {
            $categoria = escapar($categoria);
            $multiplicador = (float)$multiplicador;
            
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
                   precio = precio_costo * $multiplicador,
                   precio_cuota = (precio_costo * $multiplicador) / cuotas
                   WHERE categoria = '$categoria' AND precio_costo IS NOT NULL");
        }
        
        $mensaje = "Márgenes actualizados correctamente";
        
        // Recargar márgenes
        $margenes = obtenerResultados("SELECT * FROM margenes_categoria ORDER BY categoria");
    }
    
    if (isset($_POST['actualizar_global'])) {
        $multiplicador_global = (float)$_POST['multiplicador_global'];
        
        if ($multiplicador_global >= 1.0) {
            // Actualizar todos los productos
            query("UPDATE productos SET 
                   multiplicador = $multiplicador_global,
                   precio = precio_costo * $multiplicador_global,
                   precio_cuota = (precio_costo * $multiplicador_global) / cuotas
                   WHERE precio_costo IS NOT NULL");
            
            // Actualizar todas las categorías
            foreach ($categorias as $cat) {
                $categoria = escapar($cat['categoria']);
                
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
            
            $mensaje = "Margen global aplicado correctamente";
            
            // Recargar márgenes
            $margenes = obtenerResultados("SELECT * FROM margenes_categoria ORDER BY categoria");
        } else {
            $error = "El multiplicador global debe ser mayor o igual a 1.0";
        }
    }
}

// Convertir márgenes a un array asociativo para fácil acceso
$margenes_por_categoria = [];
foreach ($margenes as $margen) {
    $margenes_por_categoria[$margen['categoria']] = $margen['multiplicador'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Márgenes - Bear Shop</title>
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
            max-width: 1000px;
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
            margin-bottom: 30px;
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
        .form-group input[type="number"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
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
        .form-row input {
            flex: 1;
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
        .info-box {
            background-color: #e3f2fd;
            border-left: 4px solid #1565c0;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box h4 {
            margin-top: 0;
            color: #1565c0;
        }
        .info-box p {
            margin-bottom: 0;
        }
        .categoria-header {
            text-transform: capitalize;
            color: #945a42;
            margin-top: 0;
        }
        .margin-preview {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }
        .margin-preview p {
            margin: 5px 0;
        }
        .margin-preview .example {
            font-style: italic;
            color: #666;
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
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($mensaje): ?>
            <div class="success-message">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>
        
        <div class="info-box">
            <h4>¿Cómo funcionan los márgenes?</h4>
            <p>El precio de venta se calcula multiplicando el precio de costo por el multiplicador. Por ejemplo, si el costo es $100 y el multiplicador es 2.0, el precio de venta será $200.</p>
        </div>
        
        <!-- Formulario para actualizar margen global -->
        <div class="form-container">
            <h3>Actualizar Margen Global</h3>
            <p>Aplica el mismo multiplicador a todas las categorías y productos.</p>
            
            <form method="POST">
                <div class="form-row">
                    <label for="multiplicador_global">Multiplicador Global:</label>
                    <input type="number" id="multiplicador_global" name="multiplicador_global" step="0.01" min="1.0" value="2.0" required>
                </div>
                
                <div class="margin-preview">
                    <p>Con un multiplicador de <span id="preview_multiplicador">2.0</span>:</p>
                    <p class="example">Un producto con costo de $1000 se venderá a $<span id="preview_precio">2000</span></p>
                    <p class="example">Margen de ganancia: <span id="preview_porcentaje">100</span>%</p>
                </div>
                
                <button type="submit" name="actualizar_global" class="submit-btn">
                    <i class="fas fa-sync-alt"></i> Aplicar Margen Global
                </button>
            </form>
        </div>
        
        <!-- Formulario para actualizar márgenes por categoría -->
        <div class="form-container">
            <h3>Márgenes por Categoría</h3>
            <p>Configura multiplicadores específicos para cada categoría de productos.</p>
            
            <form method="POST">
                <?php foreach ($categorias as $categoria): ?>
                    <div class="form-group">
                        <h4 class="categoria-header"><?php echo $categoria['categoria']; ?></h4>
                        <div class="form-row">
                            <label for="multiplicador_<?php echo $categoria['categoria']; ?>">Multiplicador:</label>
                            <input type="number" 
                                   id="multiplicador_<?php echo $categoria['categoria']; ?>" 
                                   name="multiplicador[<?php echo $categoria['categoria']; ?>]" 
                                   step="0.01" 
                                   min="1.0" 
                                   value="<?php echo isset($margenes_por_categoria[$categoria['categoria']]) ? $margenes_por_categoria[$categoria['categoria']] : '2.0'; ?>" 
                                   required
                                   class="multiplicador-categoria"
                                   data-categoria="<?php echo $categoria['categoria']; ?>">
                        </div>
                        
                        <div class="margin-preview" id="preview_<?php echo $categoria['categoria']; ?>">
                            <p>Con un multiplicador de <span class="preview-mult"><?php echo isset($margenes_por_categoria[$categoria['categoria']]) ? $margenes_por_categoria[$categoria['categoria']] : '2.0'; ?></span>:</p>
                            <p class="example">Un producto con costo de $1000 se venderá a $<span class="preview-precio"><?php echo isset($margenes_por_categoria[$categoria['categoria']]) ? $margenes_por_categoria[$categoria['categoria']] * 1000 : '2000'; ?></span></p>
                            <p class="example">Margen de ganancia: <span class="preview-porcentaje"><?php echo isset($margenes_por_categoria[$categoria['categoria']]) ? ($margenes_por_categoria[$categoria['categoria']] - 1) * 100 : '100'; ?></span>%</p>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <button type="submit" name="actualizar_margenes" class="submit-btn">
                    <i class="fas fa-save"></i> Guardar Márgenes por Categoría
                </button>
            </form>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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
                    const preview = document.getElementById('preview_' + categoria);
                    const valor = parseFloat(this.value) || 0;
                    
                    preview.querySelector('.preview-mult').textContent = valor.toFixed(2);
                    preview.querySelector('.preview-precio').textContent = (valor * 1000).toFixed(0);
                    preview.querySelector('.preview-porcentaje').textContent = ((valor - 1) * 100).toFixed(0);
                });
            });
        });
    </script>
</body>
</html>