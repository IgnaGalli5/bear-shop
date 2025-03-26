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

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $nombre = escapar($_POST['nombre']);
    $precio_costo = (float)$_POST['precio_costo'];
    $multiplicador = (float)$_POST['multiplicador'];
    $precio = $precio_costo * $multiplicador;
    $cuotas = (int)$_POST['cuotas'];
    $precio_cuota = $precio / $cuotas;
    $categoria = escapar($_POST['categoria']);
    $descripcion = escapar($_POST['descripcion']);
    $caracteristicas = escapar($_POST['caracteristicas']);
    $modo_uso = escapar($_POST['modo_uso']);
    $calificacion = (float)$_POST['calificacion'];
    $num_calificaciones = (int)$_POST['num_calificaciones'];

    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre del producto es obligatorio.';
    } else if ($precio_costo <= 0) {
        $error = 'El precio de costo debe ser mayor que cero.';
    } else if ($multiplicador <= 0) {
        $error = 'El multiplicador debe ser mayor que cero.';
    }

    // Manejar la imagen
    $imagen = 'productos/default.jpg'; // Imagen por defecto

    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        $archivo = $_FILES['imagen'];
        $nombre_archivo = $archivo['name'];
        $tipo_archivo = $archivo['type'];
        $tamano_archivo = $archivo['size'];
        $temp_archivo = $archivo['tmp_name'];

        // Verificar tipo de archivo
        $extensiones_permitidas = ['image/jpeg', 'image/jpg', 'image/png'];
        if (!in_array($tipo_archivo, $extensiones_permitidas)) {
            $error = 'Tipo de archivo no permitido. Solo se permiten JPG y PNG.';
        } else {
            // Crear directorio si no existe
            $directorio_destino = '../productos/';
            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            // Generar nombre único
            $nombre_unico = uniqid() . '_' . $nombre_archivo;
            $ruta_destino = $directorio_destino . $nombre_unico;

            // Mover archivo
            if (move_uploaded_file($temp_archivo, $ruta_destino)) {
                $imagen = 'productos/' . $nombre_unico;
            } else {
                $error = 'Error al subir la imagen.';
            }
        }
    }

    // Si no hay errores, insertar en la base de datos
    // Si no hay errores, insertar en la base de datos
  if (empty($error)) {
    $sql = "INSERT INTO productos (nombre, precio_costo, multiplicador, precio, cuotas, precio_cuota, imagen, categoria, descripcion, caracteristicas, modo_uso, calificacion, num_calificaciones) 
            VALUES ('$nombre', $precio_costo, $multiplicador, $precio, $cuotas, $precio_cuota, '$imagen', '$categoria', '$descripcion', '$caracteristicas', '$modo_uso', $calificacion, $num_calificaciones)";
    
    if (query($sql)) {
        $exito = 'Producto agregado correctamente.';
        // Redireccionar después de 2 segundos
        header('Refresh: 2; URL=productos.php?mensaje=Producto agregado correctamente');
    } else {
        $error = 'Error al agregar el producto.';
    }
}
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agregar Producto - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos básicos (similares a los anteriores) */
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
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
            <h2 class="page-title">Agregar Nuevo Producto</h2>
            <p>Completa el formulario para agregar un nuevo producto a la tienda.</p>
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
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>



               
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio_costo">Precio de Costo *</label>
                        <input type="number" id="precio_costo" name="precio_costo" step="0.01" min="0.01" required>
                        <small>Este valor es obligatorio y debe ser mayor que cero.</small>
                    </div>

                    <div class="form-group">
                        <label for="multiplicador">Multiplicador</label>
                        <input type="number" id="multiplicador" name="multiplicador" step="0.01" value="2.0" min="1.0">
                        <small>Factor por el que se multiplica el costo para obtener el precio de venta</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="precio">Precio de Venta *</label>
                    <input type="number" id="precio" name="precio" step="0.01" required readonly>
                    <small>Este valor se calcula automáticamente (Costo × Multiplicador)</small>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría *</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccionar categoría</option>
                        <option value="skincare">Skin Care</option>
                        <option value="maquillaje">Maquillaje</option>
                        <option value="accesorios">Accesorios</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="imagen">Imagen del Producto</label>
                    <input type="file" id="imagen" name="imagen">
                    <small>Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB.</small>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" required></textarea>
                </div>

                <div class="form-group">
                    <label for="caracteristicas">Características</label>
                    <textarea id="caracteristicas" name="caracteristicas" placeholder="Escribe cada característica en una línea nueva"></textarea>
                </div>

                <div class="form-group">
                    <label for="modo_uso">Modo de Uso</label>
                    <textarea id="modo_uso" name="modo_uso"></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="calificacion">Calificación</label>
                        <input type="number" id="calificacion" name="calificacion" step="0.1" min="0" max="5" value="5.0">
                    </div>

                    min="0" max="5" value="5.0">
                </div>

                <div class="form-group">
                    <label for="num_calificaciones">Número de Calificaciones</label>
                    <input type="number" id="num_calificaciones" name="num_calificaciones" value="0" min="0">
                </div>
        </div>

        <button type="submit" class="submit-btn">
            <i class="fas fa-save"></i> Guardar Producto
        </button>
        </form>
    </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const precioCostoInput = document.getElementById('precio_costo');
    const multiplicadorInput = document.getElementById('multiplicador');
    const precioInput = document.getElementById('precio');
    
    // Función para calcular el precio
    function calcularPrecio() {
        const costo = parseFloat(precioCostoInput.value) || 0;
        const multiplicador = parseFloat(multiplicadorInput.value) || 0;
        
        if (costo > 0 && multiplicador > 0) {
            const precio = costo * multiplicador;
            precioInput.value = precio.toFixed(2);
        }
    }
    
    // Eventos para recalcular el precio
    precioCostoInput.addEventListener('input', calcularPrecio);
    multiplicadorInput.addEventListener('input', calcularPrecio);
});
</script>
</body>

</html>

