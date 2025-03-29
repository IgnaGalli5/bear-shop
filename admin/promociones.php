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

// Buscar la sección donde se muestra la tabla de promociones
// Modificar la consulta para obtener el conteo de productos por promoción

// Reemplazar la línea:
// $promociones = obtenerResultados("SELECT * FROM promociones ORDER BY fecha_creacion DESC");

// Con esta consulta mejorada:
$promociones = obtenerResultados("
  SELECT p.*, 
         (SELECT COUNT(*) FROM productos WHERE promocion_id = p.id) as total_productos 
  FROM promociones p 
  ORDER BY p.fecha_creacion DESC
");

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de Promociones - Bear Shop</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    /* Estilos base */
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
    }
    
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
      transition: all 0.2s ease;
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
    
    /* Tabla */
    .table-container {
      overflow-x: auto;
      margin-bottom: 30px;
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 10px var(--shadow);
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      min-width: 800px;
    }
    
    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid var(--gray);
    }
    
    th {
      background-color: var(--primary);
      color: var(--white);
      font-weight: normal;
      white-space: nowrap;
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover {
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
      min-width: 80px;
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
    
    /* Tarjetas para vista móvil */
    .promo-cards {
      display: none;
      flex-direction: column;
      gap: 15px;
      margin-bottom: 30px;
    }
    
    .promo-card {
      background-color: var(--white);
      border-radius: 8px;
      box-shadow: 0 2px 10px var(--shadow);
      padding: 15px;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    
    .promo-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 5px 15px var(--shadow);
    }
    
    .promo-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
      border-bottom: 1px solid var(--gray);
      padding-bottom: 10px;
    }
    
    .promo-card-title {
      font-size: 18px;
      font-weight: bold;
      color: var(--primary);
    }
    
    .promo-card-body {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
      margin-bottom: 15px;
    }
    
    .promo-card-item {
      display: flex;
      flex-direction: column;
    }
    
    .promo-card-label {
      font-size: 12px;
      color: var(--dark-gray);
      margin-bottom: 2px;
    }
    
    .promo-card-value {
      font-weight: bold;
    }
    
    .promo-card-actions {
      display: flex;
      flex-wrap: wrap;
      gap: 5px;
      justify-content: flex-end;
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
        gap: 10px;
      }
      
      .page-title {
        font-size: 24px;
      }
      
      .table-container {
        display: none;
      }
      
      .promo-cards {
        display: flex;
      }
      
      .mobile-fab {
        display: flex;
      }
      
      .btn {
        padding: 6px 12px;
        font-size: 14px;
      }
    }
    
    @media (max-width: 480px) {
      .promo-card-body {
        grid-template-columns: 1fr;
      }
      
      .promo-card-actions {
        justify-content: center;
      }
      
      .page-title {
        font-size: 20px;
        text-align: center;
        width: 100%;
      }
      
      .page-header .btn {
        width: 100%;
        justify-content: center;
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
          <i class="fas fa-box"></i> Productos
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
    
    <!-- Vista de tabla para escritorio -->
    <div class="table-container">
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
          <?php if (empty($promociones)): ?>
            <tr>
              <td colspan="8" style="text-align: center;">No hay promociones registradas</td>
            </tr>
          <?php else: ?>
            <?php foreach ($promociones as $promocion): ?>
              <?php 
                // Contar productos en esta promoción
                // Reemplazar:
                // $count_result = query("SELECT COUNT(*) as total FROM productos WHERE promocion_id = {$promocion['id']}");
                // $productos_count = $count_result->fetch_assoc()['total'];

                // Con:
                $productos_count = $promocion['total_productos'];
                
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
                
                // Formatear tipo de promoción
                switch($promocion['tipo']) {
                  case 'porcentaje':
                    $tipo_texto = 'Porcentaje';
                    break;
                  case 'monto_fijo':
                    $tipo_texto = 'Monto Fijo';
                    break;
                  case 'especial':
                    $tipo_texto = 'Especial';
                    break;
                  default:
                    $tipo_texto = $promocion['tipo'];
                }
                
                // Formatear valor
                if ($promocion['tipo'] == 'porcentaje') {
                  $valor_texto = $promocion['valor'] . '%';
                } else {
                  $valor_texto = '$' . number_format($promocion['valor'], 2, ',', '.');
                }
              ?>
              <tr>
                <td><?php echo $promocion['id']; ?></td>
                <td><?php echo $promocion['nombre']; ?></td>
                <td><?php echo $tipo_texto; ?></td>
                <td><?php echo $valor_texto; ?></td>
                <td>
                  Del <?php echo date('d/m/Y', strtotime($promocion['fecha_inicio'])); ?>
                  <br>
                  al <?php echo date('d/m/Y', strtotime($promocion['fecha_fin'])); ?>
                </td>
                <td><span class="badge <?php echo $clase_estado; ?>"><?php echo $estado; ?></span></td>
                <td>
                  <?php echo $productos_count; ?> productos
                  <?php if ($productos_count > 0): ?>
                    <a href="ver-productos-promocion.php?id=<?php echo $promocion['id']; ?>" class="btn btn-sm">
                      <i class="fas fa-eye"></i> Ver
                    </a>
                  <?php else: ?>
                    <span class="badge badge-warning">Sin productos</span>
                  <?php endif; ?>
                </td>
                <td class="actions">
                  <a href="editar-promocion.php?id=<?php echo $promocion['id']; ?>" class="btn btn-sm">
                    <i class="fas fa-edit"></i> Editar
                  </a>
                  <?php if ($promocion['activa']): ?>
                    <a href="promociones.php?toggle=<?php echo $promocion['id']; ?>" class="btn btn-warning btn-sm">
                      <i class="fas fa-toggle-off"></i> Desactivar
                    </a>
                  <?php else: ?>
                    <a href="promociones.php?toggle=<?php echo $promocion['id']; ?>" class="btn btn-success btn-sm">
                      <i class="fas fa-toggle-on"></i> Activar
                    </a>
                  <?php endif; ?>
                  <a href="promociones.php?eliminar=<?php echo $promocion['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta promoción? Se eliminarán todas las asociaciones con productos.')">
                    <i class="fas fa-trash"></i> Eliminar
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    
    <!-- Vista de tarjetas para móvil -->
    <div class="promo-cards">
      <?php if (empty($promociones)): ?>
        <div class="promo-card" style="text-align: center; padding: 30px 15px;">
          <i class="fas fa-tag" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
          <p>No hay promociones registradas</p>
          <a href="crear-promocion.php" class="btn" style="margin-top: 15px;">
            <i class="fas fa-plus"></i> Crear Nueva Promoción
          </a>
        </div>
      <?php else: ?>
        <?php foreach ($promociones as $promocion): ?>
          <?php 
            // Contar productos en esta promoción
            // Reemplazar:
            // $count_result = query("SELECT COUNT(*) as total FROM productos WHERE promocion_id = {$promocion['id']}");
            // $productos_count = $count_result->fetch_assoc()['total'];

            // Con:
            $productos_count = $promocion['total_productos'];
            
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
            
            // Formatear tipo de promoción
            switch($promocion['tipo']) {
              case 'porcentaje':
                $tipo_texto = 'Porcentaje';
                break;
              case 'monto_fijo':
                $tipo_texto = 'Monto Fijo';
                break;
              case 'especial':
                $tipo_texto = 'Especial';
                break;
              default:
                $tipo_texto = $promocion['tipo'];
            }
            
            // Formatear valor
            if ($promocion['tipo'] == 'porcentaje') {
              $valor_texto = $promocion['valor'] . '%';
            } else {
              $valor_texto = '$' . number_format($promocion['valor'], 2, ',', '.');
            }
          ?>
          <div class="promo-card">
            <div class="promo-card-header">
              <div class="promo-card-title"><?php echo $promocion['nombre']; ?></div>
              <span class="badge <?php echo $clase_estado; ?>"><?php echo $estado; ?></span>
            </div>
            
            <div class="promo-card-body">
              <div class="promo-card-item">
                <div class="promo-card-label">ID</div>
                <div class="promo-card-value"><?php echo $promocion['id']; ?></div>
              </div>
              
              <div class="promo-card-item">
                <div class="promo-card-label">Tipo</div>
                <div class="promo-card-value"><?php echo $tipo_texto; ?></div>
              </div>
              
              <div class="promo-card-item">
                <div class="promo-card-label">Valor</div>
                <div class="promo-card-value"><?php echo $valor_texto; ?></div>
              </div>
              
              <div class="promo-card-item">
                <div class="promo-card-label">Productos</div>
                <div class="promo-card-value">
                  <?php echo $productos_count; ?> productos
                  <?php if ($productos_count == 0): ?>
                    <span class="badge badge-warning" style="margin-left: 5px;">Sin productos</span>
                  <?php endif; ?>
                </div>
              </div>
              
              <div class="promo-card-item">
                <div class="promo-card-label">Fecha inicio</div>
                <div class="promo-card-value"><?php echo date('d/m/Y', strtotime($promocion['fecha_inicio'])); ?></div>
              </div>
              
              <div class="promo-card-item">
                <div class="promo-card-label">Fecha fin</div>
                <div class="promo-card-value"><?php echo date('d/m/Y', strtotime($promocion['fecha_fin'])); ?></div>
              </div>
            </div>
            
            <div class="promo-card-actions">
              <a href="ver-productos-promocion.php?id=<?php echo $promocion['id']; ?>" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i> Ver Productos
              </a>
              
              <a href="editar-promocion.php?id=<?php echo $promocion['id']; ?>" class="btn btn-sm">
                <i class="fas fa-edit"></i> Editar
              </a>
              
              <?php if ($promocion['activa']): ?>
                <a href="promociones.php?toggle=<?php echo $promocion['id']; ?>" class="btn btn-warning btn-sm">
                  <i class="fas fa-toggle-off"></i> Desactivar
                </a>
              <?php else: ?>
                <a href="promociones.php?toggle=<?php echo $promocion['id']; ?>" class="btn btn-success btn-sm">
                  <i class="fas fa-toggle-on"></i> Activar
                </a>
              <?php endif; ?>
              
              <a href="promociones.php?eliminar=<?php echo $promocion['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta promoción? Se eliminarán todas las asociaciones con productos.')">
                <i class="fas fa-trash"></i> Eliminar
              </a>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
  
  <!-- Botón flotante para móvil -->
  <a href="crear-promocion.php" class="mobile-fab">
    <i class="fas fa-plus"></i>
  </a>
</body>
</html>

