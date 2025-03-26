<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
  header('Location: index.php');
  exit;
}

// Eliminar promoción si se solicita
if (isset($_GET['eliminar'])) {
  $id = (int)$_GET['eliminar'];
  query("DELETE FROM promociones WHERE id = $id");
  
  // También eliminar la asociación con productos
  query("UPDATE productos SET promocion_id = NULL, precio_promocion = NULL WHERE promocion_id = $id");
  
  header('Location: promociones.php?mensaje=Promoción eliminada correctamente');
  exit;
}

// Activar/desactivar promoción
if (isset($_GET['toggle'])) {
  $id = (int)$_GET['toggle'];
  $promocion = obtenerResultados("SELECT activa FROM promociones WHERE id = $id")[0];
  $nuevo_estado = $promocion['activa'] ? 0 : 1;
  
  query("UPDATE promociones SET activa = $nuevo_estado WHERE id = $id");
  
  $mensaje = $nuevo_estado ? "Promoción activada correctamente" : "Promoción desactivada correctamente";
  header("Location: promociones.php?mensaje=$mensaje");
  exit;
}

// Obtener todas las promociones
$promociones = obtenerResultados("SELECT * FROM promociones ORDER BY fecha_creacion DESC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Promociones - Bear Shop</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
      /* Estilos básicos (similares a productos.php) */
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
      .btn-success {
          background-color: #4CAF50;
          color: white;
      }
      .btn-success:hover {
          background-color: #388E3C;
      }
      .btn-warning {
          background-color: #FF9800;
          color: white;
      }
      .btn-warning:hover {
          background-color: #F57C00;
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
  </style>
</head>
<body>
  <header>
      <div class="header-content">
          <div class="logo">
              <h1>BEAR SHOP - ADMIN</h1>
          </div>
          <div class="user-info">
              <a href="dashboard.php" class="btn">Dashboard</a>
              <a href="logout.php" class="btn">Cerrar Sesión</a>
          </div>
      </div>
  </header>
  
  <div class="container">
      <div class="page-header">
          <h2 class="page-title">Gestión de Promociones</h2>
          <a href="crear-promocion.php" class="btn">
              <i class="fas fa-plus"></i> Crear Nueva Promoción
          </a>
      </div>
      
      <?php if (isset($_GET['mensaje'])): ?>
          <div class="message">
              <?php echo $_GET['mensaje']; ?>
          </div>
      <?php endif; ?>
      
      <table>
          <thead>
              <tr>
                  <th>ID</th>
                  <th>Nombre</th>
                  <th>Tipo</th>
                  <th>Valor</th>
                  <th>Fechas</th>
                  <th>Estado</th>
                  <th>Productos</th>
                  <th>Acciones</th>
              </tr>
          </thead>
          <tbody>
              <?php foreach ($promociones as $promocion): ?>
              <?php 
                  // Contar productos en esta promoción
                  $count_result = query("SELECT COUNT(*) as total FROM productos WHERE promocion_id = {$promocion['id']}");
                  $productos_count = $count_result->fetch_assoc()['total'];
                  
                  // Determinar si la promoción está activa y vigente
                  $hoy = date('Y-m-d');
                  $estado = 'Inactiva';
                  $clase_estado = 'badge-danger';
                  
                  if ($promocion['activa']) {
                      if ($promocion['fecha_inicio'] <= $hoy && $promocion['fecha_fin'] >= $hoy) {
                          $estado = 'Activa';
                          $clase_estado = 'badge-success';
                      } else if ($promocion['fecha_inicio'] > $hoy) {
                          $estado = 'Pendiente';
                          $clase_estado = 'badge-warning';
                      } else if ($promocion['fecha_fin'] < $hoy) {
                          $estado = 'Vencida';
                          $clase_estado = 'badge-danger';
                      }
                  }
              ?>
              <tr>
                  <td><?php echo $promocion['id']; ?></td>
                  <td><?php echo $promocion['nombre']; ?></td>
                  <td>
                      <?php 
                          switch($promocion['tipo']) {
                              case 'porcentaje':
                                  echo 'Porcentaje';
                                  break;
                              case 'monto_fijo':
                                  echo 'Monto Fijo';
                                  break;
                              case 'especial':
                                  echo 'Especial';
                                  break;
                              default:
                                  echo $promocion['tipo'];
                          }
                      ?>
                  </td>
                  <td>
                      <?php 
                          if ($promocion['tipo'] == 'porcentaje') {
                              echo $promocion['valor'] . '%';
                          } else {
                              echo '$' . number_format($promocion['valor'], 2, ',', '.');
                          }
                      ?>
                  </td>
                  <td>
                      Del <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio'])); ?>
                      <br>
                      al <?php echo date('d/m/Y', strtotime($promocion['fecha_fin'])); ?>
                  </td>
                  <td><span class="badge <?php echo $clase_estado; ?>"><?php echo $estado; ?></span></td>
                  <td>
                      <?php echo $productos_count; ?> productos
                      <a href="ver-productos-promocion.php?id=<?php echo $promocion['id']; ?>" class="btn btn-sm">
                          <i class="fas fa-eye"></i> Ver
                      </a>
                  </td>
                  <td class="actions">
                      <a href="editar-promocion.php?id=<?php echo $promocion['id']; ?>" class="btn">
                          <i class="fas fa-edit"></i> Editar
                      </a>
                      <?php if ($promocion['activa']): ?>
                          <a href="promociones.php?toggle=<?php echo $promocion['id']; ?>" class="btn btn-warning">
                              <i class="fas fa-toggle-off"></i> Desactivar
                          </a>
                      <?php else: ?>
                          <a href="promociones.php?toggle=<?php echo $promocion['id']; ?>" class="btn btn-success">
                              <i class="fas fa-toggle-on"></i> Activar
                          </a>
                      <?php endif; ?>
                      <a href="promociones.php?eliminar=<?php echo $promocion['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar esta promoción? Se eliminarán todas las asociaciones con productos.')">
                          <i class="fas fa-trash"></i> Eliminar
                      </a>
                  </td>
              </tr>
              <?php endforeach; ?>
              
              <?php if (empty($promociones)): ?>
              <tr>
                  <td colspan="8" style="text-align: center;">No hay promociones registradas</td>
              </tr>
              <?php endif; ?>
          </tbody>
      </table>
  </div>
</body>
</html>