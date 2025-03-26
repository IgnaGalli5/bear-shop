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
  $tipo = escapar($_POST['tipo']);
  $valor = (float)$_POST['valor'];
  $fecha_inicio = escapar($_POST['fecha_inicio']);
  $fecha_fin = escapar($_POST['fecha_fin']);
  $activa = isset($_POST['activa']) ? 1 : 0;
  $categoria = !empty($_POST['categoria']) ? escapar($_POST['categoria']) : NULL;
  $descripcion = !empty($_POST['descripcion']) ? escapar($_POST['descripcion']) : NULL;
  $destacada = isset($_POST['destacada']) ? 1 : 0;
  $margen_minimo = !empty($_POST['margen_minimo']) ? (float)$_POST['margen_minimo'] : 1.30;
  
  // Validar fechas
  if (strtotime($fecha_fin) < strtotime($fecha_inicio)) {
    $error = 'La fecha de fin no puede ser anterior a la fecha de inicio.';
  }
  
  // Si no hay errores, insertar en la base de datos
  if (empty($error)) {
    $sql = "INSERT INTO promociones (nombre, tipo, valor, fecha_inicio, fecha_fin, activa, categoria, descripcion, destacada, margen_minimo) 
            VALUES ('$nombre', '$tipo', $valor, '$fecha_inicio', '$fecha_fin', $activa, " . 
            ($categoria === NULL ? "NULL" : "'$categoria'") . ", " . 
            ($descripcion === NULL ? "NULL" : "'$descripcion'") . ", $destacada, $margen_minimo)";
    
    if (query($sql)) {
      $promocion_id = mysqli_insert_id(conectarDB());
      $exito = 'Promoción creada correctamente.';
      
      // Redireccionar después de 2 segundos
      header('Refresh: 2; URL=promociones.php?mensaje=Promoción creada correctamente');
    } else {
      $error = 'Error al crear la promoción.';
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
  <title>Crear Promoción - Bear Shop</title>
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
          align-items: center;
          margin-bottom: 20px;
      }
      .checkbox-group input[type="checkbox"] {
          margin-right: 10px;
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
      .help-text {
          font-size: 0.85rem;
          color: #666;
          margin-top: 5px;
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
          <h2 class="page-title">Crear Nueva Promoción</h2>
          <p>Completa el formulario para crear una nueva promoción.</p>
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
          <form method="POST">
              <div class="form-group">
                  <label for="nombre">Nombre de la Promoción *</label>
                  <input type="text" id="nombre" name="nombre" required>
                  <p class="help-text">Ejemplo: "Descuento de Verano", "2x1 en Maquillaje", etc.</p>
              </div>
              
              <div class="form-row">
                  <div class="form-group">
                      <label for="tipo">Tipo de Promoción *</label>
                      <select id="tipo" name="tipo" required>
                          <option value="">Seleccionar tipo</option>
                          <option value="porcentaje">Porcentaje de descuento</option>
                          <option value="monto_fijo">Monto fijo de descuento</option>
                          <option value="especial">Promoción especial</option>
                      </select>
                  </div>
                  
                  <div class="form-group">
                      <label for="valor">Valor *</label>
                      <input type="number" id="valor" name="valor" step="0.01" required>
                      <p class="help-text">Para porcentaje: 20 (significa 20%). Para monto fijo: valor en pesos.</p>
                  </div>
              </div>
              
              <div class="form-row">
                  <div class="form-group">
                      <label for="fecha_inicio">Fecha de Inicio *</label>
                      <input type="date" id="fecha_inicio" name="fecha_inicio" required>
                  </div>
                  
                  <div class="form-group">
                      <label for="fecha_fin">Fecha de Fin *</label>
                      <input type="date" id="fecha_fin" name="fecha_fin" required>
                  </div>
              </div>
              
              <div class="form-group">
                  <label for="categoria">Categoría (opcional)</label>
                  <select id="categoria" name="categoria">
                      <option value="">Todas las categorías</option>
                      <?php foreach ($categorias as $cat): ?>
                          <option value="<?php echo $cat['categoria']; ?>"><?php echo ucfirst($cat['categoria']); ?></option>
                      <?php endforeach; ?>
                  </select>
                  <p class="help-text">Si seleccionas una categoría, la promoción solo se aplicará a productos de esa categoría.</p>
              </div>
              
              <div class="form-group">
                  <label for="descripcion">Descripción (opcional)</label>
                  <textarea id="descripcion" name="descripcion"></textarea>
              </div>
              
              <div class="form-group">
                  <label for="margen_minimo">Margen Mínimo</label>
                  <input type="number" id="margen_minimo" name="margen_minimo" step="0.01" value="1.30">
                  <p class="help-text">Factor mínimo sobre el costo para aplicar la promoción (por defecto 1.30 = 30% sobre el costo).</p>
              </div>
              
              <div class="checkbox-group">
                  <input type="checkbox" id="activa" name="activa" checked>
                  <label for="activa">Promoción activa</label>
              </div>
              
              <div class="checkbox-group">
                  <input type="checkbox" id="destacada" name="destacada">
                  <label for="destacada">Promoción destacada</label>
                  <p class="help-text">Las promociones destacadas se muestran en la página principal.</p>
              </div>
              
              <button type="submit" class="submit-btn">
                  <i class="fas fa-save"></i> Guardar Promoción
              </button>
          </form>
      </div>
  </div>
</body>
</html>