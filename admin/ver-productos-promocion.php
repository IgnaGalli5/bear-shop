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

// Agregar producto a la promoción
if (isset($_POST['agregar_producto'])) {
  $producto_id = (int)$_POST['producto_id'];
  $precio_promocion = (float)$_POST['precio_promocion'];
  
  // Actualizar el producto
  query("UPDATE productos SET promocion_id = $id, precio_promocion = $precio_promocion WHERE id = $producto_id");
  
  header("Location: ver-productos-promocion.php?id=$id&mensaje=Producto agregado a la promoción");
  exit;
}

// Quitar producto de la promoción
if (isset($_GET['quitar'])) {
  $producto_id = (int)$_GET['quitar'];
  
  // Actualizar el producto
  query("UPDATE productos SET promocion_id = NULL, precio_promocion = NULL WHERE id = $producto_id");
  
  header("Location: ver-productos-promocion.php?id=$id&mensaje=Producto quitado de la promoción");
  exit;
}

// Obtener productos en esta promoción
$productos_promocion = obtenerResultados("
  SELECT * FROM productos 
  WHERE promocion_id = $id 
  ORDER BY nombre
");

// Obtener productos que no están en ninguna promoción
$productos_disponibles = obtenerResultados("
  SELECT * FROM productos 
  WHERE promocion_id IS NULL 
  " . ($promocion['categoria'] ? "AND categoria = '{$promocion['categoria']}'" : "") . "
  ORDER BY nombre
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Productos en Promoción - Bear Shop</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Variables CSS */
    :root {
      --primary: #945a42;
      --primary-dark: #7a4a37;
      --primary-light: #eec8a3;
      --secondary: #e5b78e;
      --success: #4CAF50;
      --success-dark: #388E3C;
      --danger: #f44336;
      --danger-dark: #d32f2f;
      --warning: #FF9800;
      --warning-dark: #F57C00;
      --info: #2196F3;
      --info-dark: #1976D2;
      --white: #ffffff;
      --light-gray: #f5f5f5;
      --gray: #eee;
      --dark-gray: #666;
      --black: #333;
      --shadow: rgba(0,0,0,0.1);
      --shadow-dark: rgba(0,0,0,0.2);
      --transition: all 0.3s ease;
    }
    
    /* Estilos base */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    
    body {
      font-family: 'Arial', sans-serif;
      background-color: var(--light-gray);
      color: var(--black);
      margin: 0;
      padding: 0;
      line-height: 1.6;
    }
    
    .container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 20px;
    }
    
    /* Header */
    header {
      background-color: var(--primary);
      color: var(--white);
      padding: 15px 0;
      margin-bottom: 30px;
      box-shadow: 0 2px 10px var(--shadow);
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
    
    /* Botones */
    .btn {
      background-color: var(--primary-light);
      color: var(--primary);
      border: none;
      padding: 8px 15px;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      font-weight: bold;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 5px;
      transition: var(--transition);
    }
    
    .btn:hover {
      background-color: var(--secondary);
      transform: translateY(-2px);
      box-shadow: 0 2px 5px var(--shadow);
    }
    
    .btn-sm {
      padding: 4px 8px;
      font-size: 12px;
    }
    
    .btn-danger {
      background-color: var(--danger);
      color: var(--white);
    }
    
    .btn-danger:hover {
      background-color: var(--danger-dark);
    }
    
    .btn-success {
      background-color: var(--success);
      color: var(--white);
    }
    
    .btn-success:hover {
      background-color: var(--success-dark);
    }
    
    .btn-warning {
      background-color: var(--warning);
      color: var(--white);
    }
    
    .btn-warning:hover {
      background-color: var(--warning-dark);
    }
    
    .btn-info {
      background-color: var(--info);
      color: var(--white);
    }
    
    .btn-info:hover {
      background-color: var(--info-dark);
    }
    
    /* Encabezado de página */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      flex-wrap: wrap;
      gap: 15px;
    }
    
    .page-title {
      color: var(--primary);
      margin: 0;
      font-size: 28px;
    }
    
    /* Mensajes */
    .message {
      background-color: #e8f5e9;
      color: #2e7d32;
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      border-left: 4px solid #2e7d32;
    }
    
    .message:before {
      content: '\f058';
      font-family: 'Font Awesome 6 Free';
      font-weight: 900;
      font-size: 18px;
    }
    
    /* Información de promoción */
    .promocion-info {
      background-color: #fff3e0;
      border-left: 4px solid var(--warning);
      padding: 20px;
      margin-bottom: 20px;
      border-radius: 4px;
      box-shadow: 0 2px 10px var(--shadow);
    }
    
    .promocion-info h3 {
      margin-top: 0;
      color: var(--primary);
      margin-bottom: 15px;
      font-size: 20px;
    }
    
    .promocion-info p {
      margin: 8px 0;
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
    }
    
    .promocion-info strong {
      min-width: 100px;
    }
    
    /* Formulario */
    .form-container {
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 10px var(--shadow);
      padding: 20px;
      margin-bottom: 30px;
    }
    
    .form-title {
      color: var(--primary);
      margin-top: 0;
      margin-bottom: 15px;
      font-size: 18px;
      border-bottom: 1px solid var(--gray);
      padding-bottom: 10px;
    }
    
    .form-row {
      display: flex;
      gap: 15px;
      margin-bottom: 15px;
      flex-wrap: wrap;
    }
    
    .form-group {
      flex: 1;
      min-width: 250px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: var(--black);
    }
    
    .form-group select,
    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid var(--gray);
      border-radius: 4px;
      font-size: 14px;
      transition: var(--transition);
    }
    
    .form-group select:focus,
    .form-group input:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 2px rgba(148, 90, 66, 0.2);
    }
    
    /* Sección de título */
    .section-title {
      color: var(--primary);
      margin-top: 30px;
      margin-bottom: 15px;
      font-size: 20px;
      border-bottom: 2px solid var(--primary-light);
      padding-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .section-title i {
      font-size: 18px;
    }
    
    /* Tabla */
    .table-container {
      overflow-x: auto;
      margin-bottom: 30px;
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 10px var(--shadow);
    }
    
    .table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px;
    }
    
    .table th, 
    .table td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid var(--gray);
    }
    
    .table th {
      background-color: var(--primary);
      color: var(--white);
      font-weight: normal;
      white-space: nowrap;
    }
    
    .table tr:last-child td {
      border-bottom: none;
    }
    
    .table tr:hover {
      background-color: #f9f9f9;
    }
    
    .actions {
      display: flex;
      gap: 5px;
      flex-wrap: wrap;
    }
    
    /* Badges */
    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: bold;
      text-align: center;
      min-width: 60px;
    }
    
    .badge-success {
      background-color: var(--success);
      color: var(--white);
    }
    
    .badge-danger {
      background-color: var(--danger);
      color: var(--white);
    }
    
    .badge-warning {
      background-color: var(--warning);
      color: var(--white);
    }
    
    .badge-info {
      background-color: var(--info);
      color: var(--white);
    }
    
    /* Imagen de producto */
    .product-image {
      width: 60px;
      height: 60px;
      object-fit: cover;
      border-radius: 4px;
      box-shadow: 0 2px 5px var(--shadow);
    }
    
    /* Tarjetas para vista móvil */
    .product-cards {
      display: none;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 30px;
    }
    
    .product-card {
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 10px var(--shadow);
      padding: 15px;
      transition: var(--transition);
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px var(--shadow-dark);
    }
    
    .product-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      border-bottom: 1px solid var(--gray);
      padding-bottom: 10px;
    }
    
    .product-card-title {
      font-size: 16px;
      font-weight: bold;
      color: var(--primary);
    }
    
    .product-card-image {
      display: flex;
      justify-content: center;
      margin-bottom: 15px;
    }
    
    .product-card-image img {
      width: 100px;
      height: 100px;
      object-fit: cover;
      border-radius: 8px;
      box-shadow: 0 2px 5px var(--shadow);
    }
    
    .product-card-body {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 15px;
    }
    
    .product-card-item {
      display: flex;
      flex-direction: column;
    }
    
    .product-card-label {
      font-size: 12px;
      color: var(--dark-gray);
      margin-bottom: 2px;
    }
    
    .product-card-value {
      font-weight: bold;
    }
    
    .product-card-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      justify-content: center;
      margin-top: 10px;
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
      background-color: var(--primary);
      color: var(--white);
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.3);
      z-index: 999;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      text-decoration: none;
      border: none;
      transition: var(--transition);
    }
    
    .mobile-fab:hover {
      background-color: var(--primary-dark);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
    }
    
    /* Estilos responsivos */
    @media (max-width: 992px) {
      .container {
        padding: 15px;
      }
      
      .form-row {
        flex-direction: column;
      }
      
      .form-group {
        width: 100%;
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
      
      .page-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .page-title {
        font-size: 24px;
        width: 100%;
      }
      
      .table-container {
        display: none;
      }
      
      .product-cards {
        display: flex;
      }
      
      .mobile-fab {
        display: flex;
      }
      
      .btn {
        padding: 6px 12px;
        font-size: 14px;
      }
      
      .promocion-info p strong {
        min-width: 100%;
      }
    }
    
    @media (max-width: 480px) {
      .product-card-body {
        grid-template-columns: 1fr;
      }
      
      .product-card-actions {
        flex-direction: column;
      }
      
      .product-card-actions .btn {
        width: 100%;
      }
      
      .page-title {
        font-size: 20px;
      }
    }
    
    /* Tema oscuro */
    @media (prefers-color-scheme: dark) {
      :root {
        --white: #1e1e1e;
        --light-gray: #121212;
        --gray: #2c2c2c;
        --dark-gray: #999;
        --black: #e0e0e0;
        --shadow: rgba(0,0,0,0.3);
        --shadow-dark: rgba(0,0,0,0.5);
      }
      
      .promocion-info {
        background-color: rgba(255, 152, 0, 0.1);
      }
      
      .message {
        background-color: rgba(46, 125, 50, 0.1);
      }
      
      .table th {
        background-color: var(--primary-dark);
      }
      
      .table tr:hover {
        background-color: rgba(255, 255, 255, 0.05);
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
      <h2 class="page-title">Productos en Promoción</h2>
    </div>
    
    <?php if (isset($_GET['mensaje'])): ?>
      <div class="message">
        <?php echo $_GET['mensaje']; ?>
      </div>
    <?php endif; ?>
    
    <div class="promocion-info">
      <h3><?php echo $promocion['nombre']; ?></h3>
      <p>
        <strong>Tipo:</strong> 
        <?php 
          switch($promocion['tipo']) {
            case 'porcentaje':
              echo 'Porcentaje de descuento (' . $promocion['valor'] . '%)';
              break;
            case 'monto_fijo':
              echo 'Monto fijo de descuento ($' . number_format($promocion['valor'], 2, ',', '.') . ')';
              break;
            case 'especial':
              echo 'Promoción especial';
              break;
          }
        ?>
      </p>
      <p><strong>Vigencia:</strong> Del <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio'])); ?> al <?php echo date('d/m/Y', strtotime($promocion['fecha_fin'])); ?></p>
      <p>
        <strong>Estado:</strong> 
        <span class="badge <?php echo $promocion['activa'] ? 'badge-success' : 'badge-danger'; ?>">
          <?php echo $promocion['activa'] ? 'Activa' : 'Inactiva'; ?>
        </span>
      </p>
      <?php if ($promocion['categoria']): ?>
        <p><strong>Categoría:</strong> <?php echo ucfirst($promocion['categoria']); ?></p>
      <?php endif; ?>
      <?php if ($promocion['descripcion']): ?>
        <p><strong>Descripción:</strong> <?php echo $promocion['descripcion']; ?></p>
      <?php endif; ?>
    </div>
    
    <!-- Formulario para agregar productos a la promoción -->
    <div class="form-container">
      <h3 class="form-title"><i class="fas fa-plus-circle"></i> Agregar Producto a la Promoción</h3>
      
      <?php if (empty($productos_disponibles)): ?>
        <p>No hay productos disponibles para agregar a esta promoción.</p>
      <?php else: ?>
        <form method="POST">
          <div class="form-row">
            <div class="form-group">
              <label for="producto_id">Seleccionar Producto</label>
              <select id="producto_id" name="producto_id" required>
                <option value="">-- Seleccionar producto --</option>
                <?php foreach ($productos_disponibles as $producto): ?>
                  <option value="<?php echo $producto['id']; ?>" data-precio="<?php echo $producto['precio']; ?>" data-costo="<?php echo isset($producto['costo']) ? $producto['costo'] : 0; ?>">
                    <?php echo $producto['nombre']; ?> - $<?php echo number_format($producto['precio'], 2, ',', '.'); ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            
            <div class="form-group">
              <label for="precio_promocion">Precio Promocional</label>
              <input type="number" id="precio_promocion" name="precio_promocion" step="0.01" required>
            </div>
          </div>
          
          <button type="submit" name="agregar_producto" class="btn btn-success">
            <i class="fas fa-plus"></i> Agregar a Promoción
          </button>
        </form>
      <?php endif; ?>
    </div>
    
    <h3 class="section-title">
      <i class="fas fa-tag"></i> Productos en esta promoción (<?php echo count($productos_promocion); ?>)
    </h3>
    
    <?php if (empty($productos_promocion)): ?>
      <div class="form-container" style="text-align: center; padding: 30px 15px;">
        <i class="fas fa-box-open" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
        <p>No hay productos asignados a esta promoción.</p>
      </div>
    <?php else: ?>
      <!-- Vista de tabla para escritorio -->
      <div class="table-container">
        <table class="table">
          <thead>
            <tr>
              <th>ID</th>
              <th>Imagen</th>
              <th>Nombre</th>
              <th>Precio Original</th>
              <th>Precio Promocional</th>
              <th>Descuento</th>
              <th>Categoría</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($productos_promocion as $producto): ?>
            <?php 
              $descuento_porcentaje = round(100 - (($producto['precio_promocion'] / $producto['precio']) * 100));
              $descuento_monto = $producto['precio'] - $producto['precio_promocion'];
            ?>
            <tr>
              <td><?php echo $producto['id']; ?></td>
              <td>
                <img src="../<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-image">
              </td>
              <td><?php echo $producto['nombre']; ?></td>
              <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
              <td>$<?php echo number_format($producto['precio_promocion'], 2, ',', '.'); ?></td>
              <td>
                <?php echo $descuento_porcentaje; ?>% 
                <span class="badge badge-info">$<?php echo number_format($descuento_monto, 2, ',', '.'); ?></span>
              </td>
              <td><?php echo ucfirst($producto['categoria']); ?></td>
              <td class="actions">
                <a href="editar-producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-sm">
                  <i class="fas fa-edit"></i> Editar
                </a>
                <a href="ver-productos-promocion.php?id=<?php echo $id; ?>&quitar=<?php echo $producto['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de quitar este producto de la promoción?')">
                  <i class="fas fa-times"></i> Quitar
                </a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      
      <!-- Vista de tarjetas para móvil -->
      <div class="product-cards">
        <?php foreach ($productos_promocion as $producto): ?>
        <?php 
          $descuento_porcentaje = round(100 - (($producto['precio_promocion'] / $producto['precio']) * 100));
          $descuento_monto = $producto['precio'] - $producto['precio_promocion'];
        ?>
        <div class="product-card">
          <div class="product-card-header">
            <div class="product-card-title"><?php echo $producto['nombre']; ?></div>
            <div>ID: <?php echo $producto['id']; ?></div>
          </div>
          
          <div class="product-card-image">
            <img src="../<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>">
          </div>
          
          <div class="product-card-body">
            <div class="product-card-item">
              <div class="product-card-label">Precio Original</div>
              <div class="product-card-value">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></div>
            </div>
            
            <div class="product-card-item">
              <div class="product-card-label">Precio Promocional</div>
              <div class="product-card-value">$<?php echo number_format($producto['precio_promocion'], 2, ',', '.'); ?></div>
            </div>
            
            <div class="product-card-item">
              <div class="product-card-label">Descuento</div>
              <div class="product-card-value">
                <?php echo $descuento_porcentaje; ?>% 
                <span class="badge badge-info">$<?php echo number_format($descuento_monto, 2, ',', '.'); ?></span>
              </div>
            </div>
            
            <div class="product-card-item">
              <div class="product-card-label">Categoría</div>
              <div class="product-card-value"><?php echo ucfirst($producto['categoria']); ?></div>
            </div>
          </div>
          
          <div class="product-card-actions">
            <a href="editar-producto.php?id=<?php echo $producto['id']; ?>" class="btn">
              <i class="fas fa-edit"></i> Editar Producto
            </a>
            <a href="ver-productos-promocion.php?id=<?php echo $id; ?>&quitar=<?php echo $producto['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de quitar este producto de la promoción?')">
              <i class="fas fa-times"></i> Quitar de Promoción
            </a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  
  <!-- Botón flotante para móvil -->
  <a href="#" class="mobile-fab" id="mobile-add-btn">
    <i class="fas fa-plus"></i>
  </a>
  
  <script>
    // Script para calcular automáticamente el precio promocional
    document.addEventListener('DOMContentLoaded', function() {
      const productoSelect = document.getElementById('producto_id');
      const precioPromoInput = document.getElementById('precio_promocion');
      
      if (productoSelect && precioPromoInput) {
        productoSelect.addEventListener('change', function() {
          const selectedOption = this.options[this.selectedIndex];
          if (selectedOption.value) {
            const precio = parseFloat(selectedOption.getAttribute('data-precio'));
            const costo = parseFloat(selectedOption.getAttribute('data-costo') || 0);
            
            // Calcular precio promocional según el tipo de promoción
              || 0);
            
            // Calcular precio promocional según el tipo de promoción
            let precioPromo = precio;
            
            <?php if ($promocion['tipo'] === 'porcentaje'): ?>
                // Descuento porcentual
                precioPromo = precio * (1 - (<?php echo $promocion['valor']; ?> / 100));
            <?php elseif ($promocion['tipo'] === 'monto_fijo'): ?>
                // Descuento de monto fijo
                precioPromo = precio - <?php echo $promocion['valor']; ?>;
                if (precioPromo < 0) precioPromo = 0;
            <?php endif; ?>
            
            // Verificar margen mínimo si hay costo
            if (costo > 0) {
                const margenMinimo = costo * 1.2; // Asumimos un margen mínimo del 20%
                if (precioPromo < margenMinimo) {
                    precioPromo = margenMinimo;
                }
            }
            
            // Redondear a 2 decimales
            precioPromo = Math.round(precioPromo * 100) / 100;
            
            precioPromoInput.value = precioPromo;
          } else {
            precioPromoInput.value = '';
          }
        });
      }
      
      // Botón flotante para móvil
      const mobileAddBtn = document.getElementById('mobile-add-btn');
      if (mobileAddBtn) {
        mobileAddBtn.addEventListener('click', function(e) {
          e.preventDefault();
          const formContainer = document.querySelector('.form-container');
          formContainer.scrollIntoView({ behavior: 'smooth' });
        });
      }
    });
  </script>
</body>
</html>