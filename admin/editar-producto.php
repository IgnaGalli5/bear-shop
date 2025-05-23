<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    header('Location: productos.php');
    exit;
}

$id = (int)$_GET['id'];

// Obtener datos del producto
$resultado = query("SELECT * FROM productos WHERE id = $id LIMIT 1");
if ($resultado->num_rows === 0) {
    header('Location: productos.php?mensaje=Producto no encontrado');
    exit;
}

$producto = $resultado->fetch_assoc();

// Inicializar array de imágenes
$imagenes = [];
if (!empty($producto['imagen'])) {
    $imagenes[] = $producto['imagen']; // Añadir imagen principal
}

// Añadir imágenes adicionales si existen
if (!empty($producto['imagenes'])) {
    $imagenes_json = $producto['imagenes'];
    $imagenes_array = json_decode($imagenes_json, true);
    if (is_array($imagenes_array) && !empty($imagenes_array)) {
        foreach ($imagenes_array as $img) {
            if (!in_array($img, $imagenes)) { // Evitar duplicados
                $imagenes[] = $img;
            }
        }
    }
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
    $categoria = escapar($_POST['categoria']);
    $descripcion = escapar($_POST['descripcion']);
    $caracteristicas = escapar($_POST['caracteristicas']);
    $modo_uso = escapar($_POST['modo_uso']);
    $calificacion = (float)$_POST['calificacion'];
    $num_calificaciones = (int)$_POST['num_calificaciones'];
    
    // Mantener las imágenes actuales
    $imagenes_actuales = isset($_POST['imagenes_actuales']) ? $_POST['imagenes_actuales'] : [];
    
    // Mantener la imagen principal actual por defecto
    $imagen_principal = $producto['imagen'];
    
    // Manejar la imagen principal si se sube una nueva
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
                $imagen_principal = 'productos/' . $nombre_unico;
            } else {
                $error = 'Error al subir la imagen principal.';
            }
        }
    }
    
    // Preparar array de imágenes final
    $imagenes_finales = [];
    
    // Agregar imagen principal
    $imagenes_finales[] = $imagen_principal;
    
    // Agregar imágenes actuales que se mantienen
    if (!empty($imagenes_actuales)) {
        foreach ($imagenes_actuales as $img) {
            if ($img !== $imagen_principal && file_exists('../' . $img)) {
                $imagenes_finales[] = $img;
            }
        }
    }
    
    // Manejar imágenes adicionales nuevas
    if (isset($_FILES['imagenes_adicionales']) && !empty($_FILES['imagenes_adicionales']['name'][0])) {
        $total_imagenes = count($_FILES['imagenes_adicionales']['name']);
        
        for ($i = 0; $i < $total_imagenes; $i++) {
            if ($_FILES['imagenes_adicionales']['error'][$i] === 0) {
                $nombre_archivo = $_FILES['imagenes_adicionales']['name'][$i];
                $tipo_archivo = $_FILES['imagenes_adicionales']['type'][$i];
                $temp_archivo = $_FILES['imagenes_adicionales']['tmp_name'][$i];
                
                // Verificar tipo de archivo
                $extensiones_permitidas = ['image/jpeg', 'image/jpg', 'image/png'];
                if (!in_array($tipo_archivo, $extensiones_permitidas)) {
                    $error = 'Tipo de archivo no permitido en imagen adicional. Solo se permiten JPG y PNG.';
                    break;
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
                        $imagenes_finales[] = 'productos/' . $nombre_unico;
                    } else {
                        $error = 'Error al subir una imagen adicional.';
                        break;
                    }
                }
            }
        }
    }
    
    // Validaciones
    if (empty($nombre)) {
        $error = 'El nombre del producto es obligatorio.';
    } else if ($precio_costo <= 0) {
        $error = 'El precio de costo debe ser mayor que cero.';
    } else if ($multiplicador <= 0) {
        $error = 'El multiplicador debe ser mayor que cero.';
    }

    // Si no hay errores, actualizar en la base de datos
    if (empty($error)) {
        // Preparar el JSON de imágenes adicionales (excluyendo la principal)
        $imagenes_adicionales = array_slice($imagenes_finales, 1);
        $imagenes_json = json_encode($imagenes_adicionales);
        
        $sql = "UPDATE productos SET 
                nombre = '$nombre', 
                precio_costo = $precio_costo,
                multiplicador = $multiplicador,
                precio = $precio, 
                imagen = '$imagen_principal', 
                imagenes = '$imagenes_json',
                categoria = '$categoria', 
                descripcion = '$descripcion', 
                caracteristicas = '$caracteristicas', 
                modo_uso = '$modo_uso', 
                calificacion = $calificacion, 
                num_calificaciones = $num_calificaciones 
                WHERE id = $id";
        
        if (query($sql)) {
            $exito = 'Producto actualizado correctamente.';
            // Actualizar datos del producto
            $resultado = query("SELECT * FROM productos WHERE id = $id LIMIT 1");
            $producto = $resultado->fetch_assoc();
            
            // Actualizar array de imágenes
            $imagenes = [];
            if (!empty($producto['imagen'])) {
                $imagenes[] = $producto['imagen'];
            }
            if (!empty($producto['imagenes'])) {
                $imagenes_array = json_decode($producto['imagenes'], true);
                if (is_array($imagenes_array)) {
                    foreach ($imagenes_array as $img) {
                        if (!in_array($img, $imagenes)) {
                            $imagenes[] = $img;
                        }
                    }
                }
            }
            
            // Redireccionar después de 2 segundos
            header('Refresh: 2; URL=productos.php?mensaje=Producto actualizado correctamente');
        } else {
            $error = 'Error al actualizar el producto.';
        }
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Bear Shop</title>
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
            margin-bottom: 20px;
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
        .form-group textarea,
        .form-group input[type="file"] {
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
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .submit-btn:hover {
            background-color: #7a4a37;
        }
        
        /* Estilos para múltiples imágenes */
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .image-preview {
            width: 100px;
            height: 100px;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .current-images {
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .current-image-item {
            position: relative;
            display: inline-block;
            width: 100px;
            height: 100px;
        }

        .current-image-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .current-image-item .is-principal {
            position: absolute;
            top: -10px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #945a42;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            border-radius: 3px;
        }

        .current-image-item .remove-current {
            position: absolute;
            top: 5px;
            right: 5px;
            background-color: rgba(255, 255, 255, 0.8);
            color: #c62828;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }
        
        small {
            display: block;
            color: #666;
            margin-top: 5px;
            font-size: 12px;
        }
        
        /* Estilos responsivos */
        @media screen and (max-width: 992px) {
            .container {
                max-width: 100%;
            }
        }
        
        @media screen and (max-width: 768px) {
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
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .page-title {
                font-size: 24px;
                text-align: center;
            }
            
            .page-header p {
                text-align: center;
            }
            
            .current-image img {
                max-width: 100%;
                height: auto;
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
        
        @media screen and (max-width: 768px) {
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
            <h2 class="page-title">Editar Producto</h2>
            <p>Modifica los datos del producto seleccionado.</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($exito): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i> <?php echo $exito; ?>
            </div>
        <?php endif; ?>
        
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data" id="producto-form">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo $producto['nombre']; ?>" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="precio_costo">Precio de Costo *</label>
                        <input type="number" id="precio_costo" name="precio_costo" step="0.01" min="0.01" value="<?php echo $producto['precio_costo']; ?>" required>
                        <small>Este valor es obligatorio y debe ser mayor que cero.</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="multiplicador">Multiplicador</label>
                        <input type="number" id="multiplicador" name="multiplicador" step="0.01" value="<?php echo $producto['multiplicador']; ?>" min="1.0">
                        <small>Factor por el que se multiplica el costo para obtener el precio de venta</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="precio">Precio de Venta *</label>
                    <input type="number" id="precio" name="precio" step="0.01" value="<?php echo $producto['precio']; ?>" required readonly>
                    <small>Este valor se calcula automáticamente (Costo × Multiplicador)</small>
                </div>
                
                <div class="form-group">
                    <label for="categoria">Categoría *</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Seleccionar categoría</option>
                        <option value="skincare" <?php echo ($producto['categoria'] === 'skincare') ? 'selected' : ''; ?>>Skin Care</option>
                        <option value="maquillaje" <?php echo ($producto['categoria'] === 'maquillaje') ? 'selected' : ''; ?>>Maquillaje</option>
                        <option value="accesorios" <?php echo ($producto['categoria'] === 'accesorios') ? 'selected' : ''; ?>>Accesorios</option>
                    </select>
                </div>
                
                <!-- Imágenes actuales -->
                <div class="form-group">
                    <label>Imágenes Actuales</label>
                    <div class="current-images">
                        <?php if (!empty($imagenes)): ?>
                            <?php foreach ($imagenes as $index => $img): ?>
                                <div class="current-image-item">
                                    <?php if ($img === $producto['imagen']): ?>
                                        <span class="is-principal">Principal</span>
                                    <?php endif; ?>
                                    <img src="../<?php echo $img; ?>" alt="<?php echo $producto['nombre']; ?>">
                                    <input type="checkbox" name="imagenes_actuales[]" value="<?php echo $img; ?>" checked style="display: none;">
                                    <button type="button" class="remove-current" data-image="<?php echo $img; ?>">×</button>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No hay imágenes disponibles.</p>
                        <?php endif; ?>
                    </div>
                    <small>Puedes eliminar imágenes haciendo clic en la X. La imagen principal se usa como miniatura en la tienda.</small>
                </div>

                <div class="form-group">
                    <label for="imagen">Cambiar Imagen Principal</label>
                    <input type="file" id="imagen" name="imagen">
                    <small>Deja este campo vacío si no quieres cambiar la imagen principal actual.</small>
                    <div id="preview-principal" class="image-preview-container"></div>
                </div>

                <div class="form-group">
                    <label>Agregar Imágenes Adicionales</label>
                    <input type="file" id="imagenes_adicionales" name="imagenes_adicionales[]" multiple>
                    <small>Puedes seleccionar múltiples imágenes para agregar. Formatos permitidos: JPG, PNG.</small>
                    <div id="preview-adicionales" class="image-preview-container"></div>
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción *</label>
                    <textarea id="descripcion" name="descripcion" required><?php echo $producto['descripcion']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="caracteristicas">Características</label>
                    <textarea id="caracteristicas" name="caracteristicas" placeholder="Escribe cada característica en una línea nueva"><?php echo $producto['caracteristicas']; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="modo_uso">Modo de Uso</label>
                    <textarea id="modo_uso" name="modo_uso"><?php echo $producto['modo_uso']; ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="calificacion">Calificación</label>
                        <input type="number" id="calificacion" name="calificacion" step="0.1" min="0" max="5" value="<?php echo $producto['calificacion']; ?>">
                        <small>Calificación del producto (0-5)</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="num_calificaciones">Número de Calificaciones</label>
                        <input type="number" id="num_calificaciones" name="num_calificaciones" value="<?php echo $producto['num_calificaciones']; ?>" min="0">
                        <small>Cantidad de calificaciones recibidas</small>
                    </div>
                </div>
                
                <button type="submit" class="submit-btn">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
    
    <!-- Botón flotante para móvil -->
    <button type="button" class="mobile-fab" id="mobile-submit">
        <i class="fas fa-save"></i>
    </button>

    <script>
document.addEventListener('DOMContentLoaded', function() {
    const precioCostoInput = document.getElementById('precio_costo');
    const multiplicadorInput = document.getElementById('multiplicador');
    const precioInput = document.getElementById('precio');
    const mobileSubmitBtn = document.getElementById('mobile-submit');
    const form = document.getElementById('producto-form');
    const imagenPrincipalInput = document.getElementById('imagen');
    const imagenesAdicionalesInput = document.getElementById('imagenes_adicionales');
    const previewPrincipal = document.getElementById('preview-principal');
    const previewAdicionales = document.getElementById('preview-adicionales');
    const removeCurrentBtns = document.querySelectorAll('.remove-current');
    
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
    
    // Evento para el botón flotante en móvil
    mobileSubmitBtn.addEventListener('click', function() {
        form.submit();
    });
    
    // Calcular precio inicial
    calcularPrecio();
    
    // Previsualización de imagen principal
    imagenPrincipalInput.addEventListener('change', function() {
        previewPrincipal.innerHTML = '';
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewDiv = document.createElement('div');
                previewDiv.className = 'image-preview';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" alt="Vista previa">
                `;
                previewPrincipal.appendChild(previewDiv);
            }
            reader.readAsDataURL(this.files[0]);
        }
    });
    
    // Previsualización de imágenes adicionales
    imagenesAdicionalesInput.addEventListener('change', function() {
        previewAdicionales.innerHTML = '';
        if (this.files && this.files.length > 0) {
            for (let i = 0; i < this.files.length; i++) {
                const reader = new FileReader();
                const file = this.files[i];
                
                reader.onload = function(e) {
                    const previewDiv = document.createElement('div');
                    previewDiv.className = 'image-preview';
                    previewDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Vista previa ${i+1}">
                    `;
                    previewAdicionales.appendChild(previewDiv);
                }
                
                reader.readAsDataURL(file);
            }
        }
    });
    
    // Eliminar imágenes actuales
    removeCurrentBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const image = this.getAttribute('data-image');
            const checkbox = this.previousElementSibling;
            checkbox.checked = false;
            this.parentElement.style.opacity = '0.3';
        });
    });
});
</script>
</body>
</html>

