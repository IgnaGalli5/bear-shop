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
  header('Location: promociones.php');
  exit;
}

$id = (int)$_GET['id'];

// Obtener datos de la promoción
$resultado = query("SELECT * FROM promociones WHERE id = $id LIMIT 1");
if ($resultado->num_rows === 0) {
  header('Location: promociones.php?mensaje=Promoción no encontrada');
  exit;
}

$promocion = $resultado->fetch_assoc();

$error = '';
$exito = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Obtener datos del formulario
  $nombre = escapar($_POST['nombre']);
  $tipo = escapar($_POST['tipo']);
  $valor = (float)$_POST['valor'];
  $fecha_inicio = escapar($_POST['fecha_inicio']);
  $fecha_fin = escapar($_POST['fecha_fin']);
  $activa = isset($_POST['activa']) ? 1 : 0;
  $categoria = !empty($_POST['categoria']) ? escapar($_POST['categoria']) : NULL;
  $descripcion = !empty($_POST['descripcion']) ? escapar($_POST['descripcion']) : NULL;
  $destacada = isset($_POST['destacada']) ? 1 : 0;
  $margen_minimo = !empty($_POST['margen_minimo']) ? (float)$_POST['margen_minimo'] : 1.30;
  
  // Obtener tipo de aplicación
  $aplicacion = isset($_POST['aplicacion']) ? $_POST['aplicacion'] : 'mantener';
  
  // Validar fechas
  if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
    $error = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
  }
  
  // Si no hay errores, actualizar en la base de datos
  if (empty($error)) {
    $sql = "UPDATE promociones SET 
            nombre = '$nombre', 
            tipo = '$tipo', 
            valor = $valor, 
            fecha_inicio = '$fecha_inicio', 
            fecha_fin = '$fecha_fin', 
            activa = $activa, 
            categoria = " . ($categoria === NULL ? "NULL" : "'$categoria'") . ", 
            descripcion = " . ($descripcion === NULL ? "NULL" : "'$descripcion'") . ", 
            destacada = $destacada, 
            margen_minimo = $margen_minimo 
            WHERE id = $id";
    
    if (query($sql)) {
      // Si la aplicación no es 'mantener', primero quitar la promoción de todos los productos actuales
      if ($aplicacion !== 'mantener') {
        query("UPDATE productos SET promocion_id = NULL, precio_promocion = NULL WHERE promocion_id = $id");
        
        // Aplicar promoción a productos según la selección
        if ($aplicacion === 'todos') {
          // Aplicar a todos los productos
          $productos_sql = "UPDATE productos SET promocion_id = $id";
          if ($tipo === 'porcentaje') {
            $productos_sql .= ", precio_promocion = CASE WHEN precio * (1 - $valor/100) > 0 THEN precio * (1 - $valor/100) ELSE 0 END";
          } else if ($tipo === 'monto_fijo') {
            $productos_sql .= ", precio_promocion = CASE WHEN precio - $valor > 0 THEN precio - $valor ELSE 0 END";
          }
          
          // Ejecutar la consulta y verificar si fue exitosa
          if (query($productos_sql)) {
            // Contar cuántos productos se actualizaron
            $count_result = query("SELECT COUNT(*) as total FROM productos WHERE promocion_id = $id");
            $count_data = $count_result->fetch_assoc();
            $productos_actualizados = $count_data['total'];
            
            // Agregar mensaje con la cantidad de productos actualizados
            $exito .= " Se aplicó la promoción a $productos_actualizados productos.";
          } else {
            $error .= " Error al aplicar la promoción a los productos.";
          }
          
        } else if ($aplicacion === 'categoria' && !empty($_POST['categoria_aplicar'])) {
          // Aplicar a productos de una categoría
          $cat_aplicar = escapar($_POST['categoria_aplicar']);
          $productos_sql = "UPDATE productos SET promocion_id = $id";
          if ($tipo === 'porcentaje') {
            $productos_sql .= ", precio_promocion = precio * (1 - $valor/100)";
          } else if ($tipo === 'monto_fijo') {
            $productos_sql .= ", precio_promocion = precio - $valor";
          }
          $productos_sql .= " WHERE categoria = '$cat_aplicar'";
          query($productos_sql);
          
        } else if ($aplicacion === 'seleccion' && isset($_POST['productos']) && is_array($_POST['productos'])) {
          // Aplicar a productos seleccionados
          foreach ($_POST['productos'] as $producto_id) {
            $producto_id = (int)$producto_id;
            
            // Obtener precio del producto
            $producto_result = query("SELECT precio FROM productos WHERE id = $producto_id");
            if ($producto_result->num_rows > 0) {
              $producto = $producto_result->fetch_assoc();
              $precio = $producto['precio'];
              
              // Calcular precio promocional
              $precio_promocion = $precio;
              if ($tipo === 'porcentaje') {
                $precio_promocion = $precio * (1 - $valor/100);
              } else if ($tipo === 'monto_fijo') {
                $precio_promocion = $precio - $valor;
                if ($precio_promocion < 0) $precio_promocion = 0;
              }
              
              // Actualizar producto
              query("UPDATE productos SET promocion_id = $id, precio_promocion = $precio_promocion WHERE id = $producto_id");
            }
          }
        }
      } else {
        // Si se mantienen los productos pero cambia el tipo o valor de la promoción, actualizar precios
        if ($tipo === 'porcentaje') {
          query("UPDATE productos SET precio_promocion = precio * (1 - $valor/100) WHERE promocion_id = $id");
        } else if ($tipo === 'monto_fijo') {
          query("UPDATE productos SET precio_promocion = precio - $valor WHERE promocion_id = $id");
        }
      }
      
      $exito = 'Promoción actualizada correctamente.';
      // Actualizar datos de la promoción
      $resultado = query("SELECT * FROM promociones WHERE id = $id LIMIT 1");
      $promocion = $resultado->fetch_assoc();
      
      // Si la promoción se desactivó, actualizar los productos
      if (!$activa) {
        query("UPDATE productos SET precio_promocion = NULL WHERE promocion_id = $id");
      }
      
      // Redireccionar después de 2 segundos
      header('Refresh: 2; URL=promociones.php?mensaje=Promoción actualizada correctamente');
    } else {
      $error = 'Error al actualizar la promoción.';
    }
  }
}

// Obtener categorías para el select
$categorias = obtenerResultados("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != '' ORDER BY categoria");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Editar Promoción - Bear Shop</title>
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
      .form-group input[type="date"],
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
      .checkbox-group {
          display: flex;
          align-items: flex-start;
          margin-bottom: 20px;
      }
      .checkbox-group input[type="checkbox"] {
          margin-right: 10px;
          margin-top: 3px;
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
      .help-text {
          font-size: 0.85rem;
          color: #666;
          margin-top: 5px;
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
          
          .checkbox-group {
              align-items: flex-start;
          }
          
          .checkbox-group .help-text {
              margin-left: 25px;
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
              <a href="promociones.php" class="btn">
                  <i class="fas fa-arrow-left"></i> Volver a Promociones
              </a>
              <a href="dashboard.php" class="btn">Dashboard</a>
              <a href="logout.php" class="btn">Cerrar Sesión</a>
          </div>
      </div>
  </header>
  
  <div class="container">
      <div class="page-header">
          <h2 class="page-title">Editar Promoción</h2>
          <p>Modifica los datos de la promoción seleccionada.</p>
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
          <form method="POST" id="promocion-form">
              <div class="form-group">
                  <label for="nombre">Nombre de la Promoción *</label>
                  <input type="text" id="nombre" name="nombre" value="<?php echo $promocion['nombre']; ?>" required>
              </div>
              
              <div class="form-row">
                  <div class="form-group">
                      <label for="tipo">Tipo de Promoción *</label>
                      <select id="tipo" name="tipo" required>
                          <option value="">Seleccionar tipo</option>
                          <option value="porcentaje" <?php echo ($promocion['tipo'] === 'porcentaje') ? 'selected' : ''; ?>>Porcentaje de descuento</option>
                          <option value="monto_fijo" <?php echo ($promocion['tipo'] === 'monto_fijo') ? 'selected' : ''; ?>>Monto fijo de descuento</option>
                          <option value="especial" <?php echo ($promocion['tipo'] === 'especial') ? 'selected' : ''; ?>>Promoción especial</option>
                      </select>
                  </div>
                  
                  <div class="form-group">
                      <label for="valor">Valor *</label>
                      <input type="number" id="valor" name="valor" step="0.01" value="<?php echo $promocion['valor']; ?>" required>
                      <p class="help-text">Para porcentaje: 20 (significa 20%). Para monto fijo: valor en pesos.</p>
                  </div>
              </div>
              
              <div class="form-row">
                  <div class="form-group">
                      <label for="fecha_inicio">Fecha de Inicio *</label>
                      <input type="date" id="fecha_inicio" name="fecha_inicio" value="<?php echo $promocion['fecha_inicio']; ?>" required>
                  </div>
                  
                  <div class="form-group">
                      <label for="fecha_fin">Fecha de Fin *</label>
                      <input type="date" id="fecha_fin" name="fecha_fin" value="<?php echo $promocion['fecha_fin']; ?>" required>
                  </div>
              </div>
              
              <div class="form-group">
                  <label for="categoria">Categoría (opcional)</label>
                  <select id="categoria" name="categoria">
                      <option value="">Todas las categorías</option>
                      <?php foreach ($categorias as $cat): ?>
                          <option value="<?php echo $cat['categoria']; ?>" <?php echo ($promocion['categoria'] === $cat['categoria']) ? 'selected' : ''; ?>><?php echo ucfirst($cat['categoria']); ?></option>
                      <?php endforeach; ?>
                  </select>
                  <p class="help-text">Si seleccionas una categoría, la promoción solo se aplicará a productos de esa categoría.</p>
              </div>
              
              <div class="form-group">
                  <label for="descripcion">Descripción (opcional)</label>
                  <textarea id="descripcion" name="descripcion"><?php echo $promocion['descripcion']; ?></textarea>
              </div>
              
              <div class="form-group">
                  <label for="margen_minimo">Margen Mínimo</label>
                  <input type="number" id="margen_minimo" name="margen_minimo" step="0.01" value="<?php echo $promocion['margen_minimo']; ?>">
                  <p class="help-text">Factor mínimo sobre el costo para aplicar la promoción (por defecto 1.30 = 30% sobre el costo).</p>
              </div>
              
              <div class="form-group">
                  <label for="aplicacion">Aplicar promoción a:</label>
                  <select id="aplicacion" name="aplicacion" onchange="mostrarOpcionesProductos()">
                      <option value="mantener">Mantener productos actuales</option>
                      <option value="seleccion">Productos seleccionados</option>
                      <option value="categoria">Todos los productos de una categoría</option>
                      <option value="todos">Todos los productos</option>
                  </select>
              </div>

              <div id="opcion-categoria" style="display: none;" class="form-group">
                  <label for="categoria_aplicar">Seleccionar categoría:</label>
                  <select id="categoria_aplicar" name="categoria_aplicar">
                      <option value="">Seleccionar categoría</option>
                      <?php foreach ($categorias as $cat): ?>
                          <option value="<?php echo $cat['categoria']; ?>"><?php echo ucfirst($cat['categoria']); ?></option>
                      <?php endforeach; ?>
                  </select>
              </div>

              <div id="opcion-productos" style="display: none;" class="form-group">
                  <label>Seleccionar productos:</label>
                  <div class="productos-container" style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                      <div class="search-box" style="margin-bottom: 10px;">
                          <input type="text" id="buscar-productos" placeholder="Buscar productos..." style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ddd;">
                      </div>
                      <table class="productos-table" style="width: 100%;">
                          <thead>
                              <tr>
                                  <th style="width: 50px;"><input type="checkbox" id="seleccionar-todos"></th>
                                  <th>Producto</th>
                                  <th>Precio</th>
                                  <th>Categoría</th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php 
                              // Obtener todos los productos
                              $productos = obtenerResultados("SELECT id, nombre, precio, categoria, promocion_id FROM productos ORDER BY nombre");
                              foreach ($productos as $producto): 
                                  $checked = ($producto['promocion_id'] == $id) ? 'checked' : '';
                              ?>
                              <tr class="producto-item">
                                  <td><input type="checkbox" name="productos[]" value="<?php echo $producto['id']; ?>" class="producto-checkbox" <?php echo $checked; ?>></td>
                                  <td><?php echo $producto['nombre']; ?></td>
                                  <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                                  <td><?php echo ucfirst($producto['categoria']); ?></td>
                              </tr>
                              <?php endforeach; ?>
                          </tbody>
                      </table>
                  </div>
              </div>

              <div class="form-group">
                  <label>Productos actuales en esta promoción:</label>
                  <div style="margin-top: 10px; padding: 10px; background-color: #f9f9f9; border-radius: 4px;">
                      <?php
                      // Obtener productos en esta promoción
                      $productos_promocion = obtenerResultados("SELECT id, nombre, precio, precio_promocion FROM productos WHERE promocion_id = $id ORDER BY nombre");
                      if (count($productos_promocion) > 0):
                      ?>
                          <table style="width: 100%;">
                              <thead>
                                  <tr>
                                      <th>Producto</th>
                                      <th>Precio Original</th>
                                      <th>Precio Promocional</th>
                                      <th>Descuento</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php foreach ($productos_promocion as $producto): 
                                      $descuento_porcentaje = round(100 - (($producto['precio_promocion'] / $producto['precio']) * 100));
                                  ?>
                                  <tr>
                                      <td><?php echo $producto['nombre']; ?></td>
                                      <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                                      <td>$<?php echo number_format($producto['precio_promocion'], 2, ',', '.'); ?></td>
                                      <td><?php echo $descuento_porcentaje; ?>%</td>
                                  </tr>
                                  <?php endforeach; ?>
                              </tbody>
                          </table>
                      <?php else: ?>
                          <p>No hay productos asignados a esta promoción.</p>
                      <?php endif; ?>
                  </div>
              </div>
              
              <div class="checkbox-group">
                  <input type="checkbox" id="activa" name="activa" <?php echo $promocion['activa'] ? 'checked' : ''; ?>>
                  <label for="activa">Promoción activa</label>
              </div>
              
              <div class="checkbox-group">
                  <input type="checkbox" id="destacada" name="destacada" <?php echo $promocion['destacada'] ? 'checked' : ''; ?>>
                  <label for="destacada">Promoción destacada</label>
                  <p class="help-text">Las promociones destacadas se muestran en la página principal.</p>
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
    // Validación de fechas en tiempo real
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    const mobileSubmitBtn = document.getElementById('mobile-submit');
    const form = document.getElementById('promocion-form');
    
    fechaInicioInput.addEventListener('change', validarFechas);
    fechaFinInput.addEventListener('change', validarFechas);
    
    function validarFechas() {
        const fechaInicio = new Date(fechaInicioInput.value);
        const fechaFin = new Date(fechaFinInput.value);
        
        if (fechaFin < fechaInicio) {
            alert('La fecha de fin no puede ser anterior a la fecha de inicio.');
            fechaFinInput.value = fechaInicioInput.value;
        }
    }
    
    // Evento para el botón flotante en móvil
    if (mobileSubmitBtn) {
        mobileSubmitBtn.addEventListener('click', function() {
            form.submit();
        });
    }
    
    // Funcionalidad para buscar productos
    const buscarInput = document.getElementById('buscar-productos');
    if (buscarInput) {
        buscarInput.addEventListener('input', function() {
            const texto = this.value.toLowerCase();
            const filas = document.querySelectorAll('.producto-item');
            
            filas.forEach(function(fila) {
                const contenido = fila.textContent.toLowerCase();
                if (contenido.includes(texto)) {
                    fila.style.display = '';
                } else {
                    fila.style.display = 'none';
                }
            });
        });
    }
    
    // Seleccionar todos los productos
    const seleccionarTodos = document.getElementById('seleccionar-todos');
    if (seleccionarTodos) {
        seleccionarTodos.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.producto-checkbox');
            checkboxes.forEach(function(checkbox) {
                if (checkbox.closest('tr').style.display !== 'none') {
                    checkbox.checked = seleccionarTodos.checked;
                }
            });
        });
    }
});

// Función para mostrar opciones según el tipo de aplicación
function mostrarOpcionesProductos() {
    const aplicacion = document.getElementById('aplicacion').value;
    const opcionCategoria = document.getElementById('opcion-categoria');
    const opcionProductos = document.getElementById('opcion-productos');
    
    if (aplicacion === 'categoria') {
        opcionCategoria.style.display = 'block';
        opcionProductos.style.display = 'none';
    } else if (aplicacion === 'todos' || aplicacion === 'mantener') {
        opcionCategoria.style.display = 'none';
        opcionProductos.style.display = 'none';
    } else {
        opcionCategoria.style.display = 'none';
        opcionProductos.style.display = 'block';
    }
}
</script>
</body>
</html>

