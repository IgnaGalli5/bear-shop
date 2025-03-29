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
  <link rel="stylesheet" href="../css/admin-responsive.css">
<script src="../js/admin-responsive.js" defer></script>
  <style>
      /* Estilos básicos (similares a los anteriores) */
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
      .btn-danger {
          background-color: #f44336;
          color: white;
      }
      .btn-danger:hover {
          background-color: #d32f2f;
      }
      .page-header {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 20px;
      }
      .page-title {
          color: #945a42;
          margin: 0;
      }
      .message {
          background-color: #e8f5e9;
          color: #2e7d32;
          padding: 10px 15px;
          border-radius: 4px;
          margin-bottom: 20px;
      }
      table {
          width: 100%;
          border-collapse: collapse;
          background-color: white;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          border-radius: 8px;
          overflow: hidden;
          margin-bottom: 30px;
      }
      th, td {
          padding: 12px 15px;
          text-align: left;
          border-bottom: 1px solid #eee;
      }
      th {
          background-color: #945a42;
          color: white;
      }
      tr:hover {
          background-color: #f9f9f9;
      }
      .product-image {
          width: 60px;
          height: 60px;
          object-fit: cover;
          border-radius: 4px;
      }
      .actions {
          display: flex;
          gap: 10px;
      }
      .badge {
          display: inline-block;
          padding: 4px 8px;
          border-radius: 4px;
          font-size: 12px;
          font-weight: bold;
      }
      .badge-success {
          background-color: #4CAF50;
          color: white;
      }
      .badge-danger {
          background-color: #f44336;
          color: white;
      }
      .badge-warning {
          background-color: #FF9800;
          color: white;
      }
      .badge-info {
          background-color: #2196F3;
          color: white;
      }
      .promocion-info {
          background-color: #fff3e0;
          border-left: 4px solid #ff9800;
          padding: 15px;
          margin-bottom: 20px;
          border-radius: 4px;
      }
      .promocion-info h3 {
          margin-top: 0;
          color: #945a42;
      }
      .promocion-info p {
          margin: 5px 0;
      }
      .form-container {
          background-color: white;
          border-radius: 8px;
          box-shadow: 0 2px 10px rgba(0,0,0,0.1);
          padding: 20px;
          margin-bottom: 30px;
      }
      .form-title {
          color: #945a42;
          margin-top: 0;
          margin-bottom: 15px;
          font-size: 1.2rem;
      }
      .form-row {
          display: flex;
          gap: 15px;
          margin-bottom: 15px;
      }
      .form-group {
          flex: 1;
      }
      .form-group label {
          display: block;
          margin-bottom: 5px;
          font-weight: bold;
      }
      .form-group select,
      .form-group input {
          width: 100%;
          padding: 8px;
          border: 1px solid #ddd;
          border-radius: 4px;
      }
      .section-title {
          color: #945a42;
          margin-top: 30px;
          margin-bottom: 15px;
          font-size: 1.5rem;
          border-bottom: 2px solid #eec8a3;
          padding-bottom: 5px;
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
          <h2 class="page-title">Productos en Promoción</h2>
      </div>
      
      <?php if (isset($_GET['mensaje'])): ?>
          <div class="message">
              <?php echo $_GET['mensaje']; ?>
          </div>
      <?php endif; ?>
      
      <div class="promocion-info">
          <h3><?php echo $promocion['nombre']; ?></h3>
          <p><strong>Tipo:</strong> 
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
          <p><strong>Estado:</strong> <?php echo $promocion['activa'] ? 'Activa' : 'Inactiva'; ?></p>
          <?php if ($promocion['categoria']): ?>
              <p><strong>Categoría:</strong> <?php echo ucfirst($promocion['categoria']); ?></p>
          <?php endif; ?>
          <?php if ($promocion['descripcion']): ?>
              <p><strong>Descripción:</strong> <?php echo $promocion['descripcion']; ?></p>
          <?php endif; ?>
      </div>
      
      <!-- Formulario para agregar productos a la promoción -->
      <div class="form-container">
          <h3 class="form-title">Agregar Producto a la Promoción</h3>
          
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
                  
                  <button type="submit" name="agregar_producto" class="btn">
                      <i class="fas fa-plus"></i> Agregar a Promoción
                  </button>
              </form>
          <?php endif; ?>
      </div>
      
      <h3 class="section-title">Productos en esta promoción (<?php echo count($productos_promocion); ?>)</h3>
      
      <?php if (empty($productos_promocion)): ?>
          <p>No hay productos asignados a esta promoción.</p>
      <?php else: ?>
          <<table class="table responsive-table">>
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
                          <a href="editar-producto.php?id=<?php echo $producto['id']; ?>" class="btn">
                              <i class="fas fa-edit"></i> Editar
                          </a>
                          <a href="ver-productos-promocion.php?id=<?php echo $id; ?>&quitar=<?php echo $producto['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de quitar este producto de la promoción?')">
                              <i class="fas fa-times"></i> Quitar
                          </a>
                      </td>
                  </tr>
                  <?php endforeach; ?>
              </tbody>
          </table>
      <?php endif; ?>
  </div>
  
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
                          const margenMinimo = costo * <?php echo $promocion['margen_minimo']; ?>;
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
      });
  </script>
</body>
</html>