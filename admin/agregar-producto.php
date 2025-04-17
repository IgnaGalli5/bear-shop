<?php
// Habilitar visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Función para registrar errores en un archivo de log
function registrar_error($mensaje) {
    $archivo_log = '../logs/errores_' . date('Y-m-d') . '.log';
    $timestamp = date('Y-m-d H:i:s');
    $mensaje_log = "[$timestamp] $mensaje\n";
    
    // Crear directorio si no existe
    if (!file_exists('../logs/')) {
        mkdir('../logs/', 0777, true);
    }
    
    file_put_contents($archivo_log, $mensaje_log, FILE_APPEND);
}

// Registrar información del servidor y PHP
registrar_error("Versión PHP: " . phpversion());
registrar_error("Servidor: " . $_SERVER['SERVER_SOFTWARE']);
registrar_error("Límites PHP - upload_max_filesize: " . ini_get('upload_max_filesize'));
registrar_error("Límites PHP - post_max_size: " . ini_get('post_max_size'));
registrar_error("Límites PHP - memory_limit: " . ini_get('memory_limit'));

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
  try {
      registrar_error("Inicio de procesamiento de formulario");
      
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

      registrar_error("Datos del formulario procesados correctamente");

      // Validaciones
      if (empty($nombre)) {
          $error = 'El nombre del producto es obligatorio.';
      } else if ($precio_costo <= 0) {
          $error = 'El precio de costo debe ser mayor que cero.';
      } else if ($multiplicador <= 0) {
          $error = 'El multiplicador debe ser mayor que cero.';
      }

      if (!empty($error)) {
          registrar_error("Error de validación: " . $error);
          throw new Exception("Error de validación: " . $error);
      }

      // Array para almacenar las rutas de las imágenes
      $imagenes = [];
      
      // Manejar la imagen principal
      $imagen = 'productos/default.jpg'; // Imagen por defecto

      if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
          registrar_error("Procesando imagen principal");
          
          $archivo = $_FILES['imagen'];
          $nombre_archivo = $archivo['name'];
          $tipo_archivo = $archivo['type'];
          $tamano_archivo = $archivo['size'];
          $temp_archivo = $archivo['tmp_name'];

          registrar_error("Imagen principal - Nombre: $nombre_archivo, Tipo: $tipo_archivo, Tamaño: $tamano_archivo bytes");

          // Verificar tipo de archivo
          $extensiones_permitidas = ['image/jpeg', 'image/jpg', 'image/png'];
          if (!in_array($tipo_archivo, $extensiones_permitidas)) {
              $error = 'Tipo de archivo no permitido. Solo se permiten JPG y PNG.';
              registrar_error("Error: Tipo de archivo no permitido para imagen principal");
              throw new Exception($error);
          } else {
              // Crear directorio si no existe
              $directorio_destino = '../productos/';
              if (!file_exists($directorio_destino)) {
                  if (!mkdir($directorio_destino, 0777, true)) {
                      $error = 'Error al crear el directorio de destino.';
                      registrar_error("Error: No se pudo crear el directorio $directorio_destino");
                      throw new Exception($error);
                  }
              }

              // Verificar permisos del directorio
              if (!is_writable($directorio_destino)) {
                  $error = 'El directorio de destino no tiene permisos de escritura.';
                  registrar_error("Error: El directorio $directorio_destino no tiene permisos de escritura");
                  throw new Exception($error);
              }

              // Generar nombre único
              $nombre_unico = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $nombre_archivo);
              $ruta_destino = $directorio_destino . $nombre_unico;

              // Mover archivo
              if (move_uploaded_file($temp_archivo, $ruta_destino)) {
                  $imagen = 'productos/' . $nombre_unico;
                  $imagenes[] = $imagen; // Agregar a la lista de imágenes
                  registrar_error("Imagen principal subida correctamente: $imagen");
              } else {
                  $error = 'Error al subir la imagen principal: ' . error_get_last()['message'];
                  registrar_error("Error al subir imagen principal: " . error_get_last()['message']);
                  throw new Exception($error);
              }
          }
      }
      
      // Manejar imágenes adicionales
      if (isset($_FILES['imagenes_adicionales']) && !empty($_FILES['imagenes_adicionales']['name'][0])) {
          registrar_error("Procesando imágenes adicionales");
          
          $total_imagenes = count($_FILES['imagenes_adicionales']['name']);
          registrar_error("Total de imágenes adicionales: $total_imagenes");
          
          for ($i = 0; $i < $total_imagenes; $i++) {
              if ($_FILES['imagenes_adicionales']['error'][$i] === 0) {
                  $nombre_archivo = $_FILES['imagenes_adicionales']['name'][$i];
                  $tipo_archivo = $_FILES['imagenes_adicionales']['type'][$i];
                  $temp_archivo = $_FILES['imagenes_adicionales']['tmp_name'][$i];
                  
                  registrar_error("Imagen adicional $i - Nombre: $nombre_archivo, Tipo: $tipo_archivo");
                  
                  // Verificar tipo de archivo
                  $extensiones_permitidas = ['image/jpeg', 'image/jpg', 'image/png'];
                  if (!in_array($tipo_archivo, $extensiones_permitidas)) {
                      $error = 'Tipo de archivo no permitido en imagen adicional. Solo se permiten JPG y PNG.';
                      registrar_error("Error: Tipo de archivo no permitido para imagen adicional $i");
                      throw new Exception($error);
                  } else {
                      // Crear directorio si no existe
                      $directorio_destino = '../productos/';
                      if (!file_exists($directorio_destino)) {
                          if (!mkdir($directorio_destino, 0777, true)) {
                              $error = 'Error al crear el directorio de destino.';
                              registrar_error("Error: No se pudo crear el directorio $directorio_destino");
                              throw new Exception($error);
                          }
                      }
                      
                      // Generar nombre único
                      $nombre_unico = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\._-]/', '', $nombre_archivo);
                      $ruta_destino = $directorio_destino . $nombre_unico;
                      
                      // Mover archivo
                      if (move_uploaded_file($temp_archivo, $ruta_destino)) {
                          $imagenes[] = 'productos/' . $nombre_unico;
                          registrar_error("Imagen adicional $i subida correctamente: productos/$nombre_unico");
                      } else {
                          $error = 'Error al subir una imagen adicional: ' . error_get_last()['message'];
                          registrar_error("Error al subir imagen adicional $i: " . error_get_last()['message']);
                          throw new Exception($error);
                      }
                  }
              } else if ($_FILES['imagenes_adicionales']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                  // Registrar errores de carga que no sean "no file"
                  $codigo_error = $_FILES['imagenes_adicionales']['error'][$i];
                  $mensaje_error = '';
                  switch ($codigo_error) {
                      case UPLOAD_ERR_INI_SIZE:
                          $mensaje_error = 'El archivo excede el tamaño máximo permitido por PHP.';
                          break;
                      case UPLOAD_ERR_FORM_SIZE:
                          $mensaje_error = 'El archivo excede el tamaño máximo permitido por el formulario.';
                          break;
                      case UPLOAD_ERR_PARTIAL:
                          $mensaje_error = 'El archivo se subió parcialmente.';
                          break;
                      case UPLOAD_ERR_NO_TMP_DIR:
                          $mensaje_error = 'No se encontró un directorio temporal.';
                          break;
                      case UPLOAD_ERR_CANT_WRITE:
                          $mensaje_error = 'Error al escribir el archivo en el disco.';
                          break;
                      case UPLOAD_ERR_EXTENSION:
                          $mensaje_error = 'Una extensión de PHP detuvo la carga del archivo.';
                          break;
                      default:
                          $mensaje_error = 'Error desconocido.';
                  }
                  registrar_error("Error en imagen adicional $i: $mensaje_error (código $codigo_error)");
              }
          }
      }

      // Si no hay errores, insertar en la base de datos
      if (empty($error)) {
          registrar_error("Preparando inserción en base de datos");
          
          // Convertir array de imágenes a JSON para almacenar
          // Excluir la imagen principal del JSON de imágenes adicionales
          $imagenes_adicionales = array_slice($imagenes, 1);
          $imagenes_json = json_encode($imagenes_adicionales);
          
          // Verificar si la codificación JSON fue exitosa
          if ($imagenes_json === false) {
              $error = 'Error al codificar las imágenes a JSON: ' . json_last_error_msg();
              registrar_error("Error de codificación JSON: " . json_last_error_msg());
              throw new Exception($error);
          }
          
          registrar_error("JSON de imágenes adicionales generado correctamente");
          
          // Preparar la consulta SQL con valores escapados correctamente
          $sql = "INSERT INTO productos (nombre, precio_costo, multiplicador, precio, imagen, imagenes, categoria, descripcion, caracteristicas, modo_uso, calificacion, num_calificaciones) 
                  VALUES ('" . escapar($nombre) . "', " . 
                  floatval($precio_costo) . ", " . 
                  floatval($multiplicador) . ", " . 
                  floatval($precio) . ", '" . 
                  escapar($imagen) . "', '" . 
                  escapar($imagenes_json) . "', '" . 
                  escapar($categoria) . "', '" . 
                  escapar($descripcion) . "', '" . 
                  escapar($caracteristicas) . "', '" . 
                  escapar($modo_uso) . "', " . 
                  floatval($calificacion) . ", " . 
                  intval($num_calificaciones) . ")";
          
          registrar_error("Ejecutando consulta SQL");
          
          if (query($sql)) {
              $exito = 'Producto agregado correctamente.';
              registrar_error("Producto agregado correctamente a la base de datos");
              // Redireccionar después de 2 segundos
              header('Refresh: 2; URL=productos.php?mensaje=Producto agregado correctamente');
          } else {
              $error_mysql = mysqli_error($GLOBALS['conn']);
              $error = 'Error al agregar el producto: ' . $error_mysql;
              registrar_error("Error de MySQL: " . $error_mysql);
              throw new Exception($error);
          }
      }
  } catch (Exception $e) {
      $error = $e->getMessage();
      registrar_error("Excepción capturada: " . $error);
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
          box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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
      
      small {
          display: block;
          color: #666;
          margin-top: 5px;
          font-size: 12px;
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
      
      .image-preview .remove-image {
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
      
      .add-more-images {
          display: inline-block;
          margin-top: 10px;
          background-color: #eec8a3;
          color: #945a42;
          border: none;
          padding: 5px 10px;
          border-radius: 4px;
          cursor: pointer;
          font-size: 14px;
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
          <form method="POST" enctype="multipart/form-data" id="producto-form">
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
                  <label for="cuotas">Cuotas</label>
                  <input type="number" id="cuotas" name="cuotas" value="1" min="1" max="12">
                  <small>Número de cuotas disponibles para este producto</small>
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
                  <label for="imagen">Imagen Principal del Producto *</label>
                  <input type="file" id="imagen" name="imagen" required>
                  <small>Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB.</small>
                  <div id="preview-principal" class="image-preview-container"></div>
              </div>

              <div class="form-group">
                  <label>Imágenes Adicionales (Tonalidades, Ángulos, etc.)</label>
                  <input type="file" id="imagenes_adicionales" name="imagenes_adicionales[]" multiple>
                  <small>Puedes seleccionar múltiples imágenes. Formatos permitidos: JPG, PNG.</small>
                  <div id="preview-adicionales" class="image-preview-container"></div>
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
                      <small>Calificación inicial del producto (0-5)</small>
                  </div>

                  <div class="form-group">
                      <label for="num_calificaciones">Número de Calificaciones</label>
                      <input type="number" id="num_calificaciones" name="num_calificaciones" value="0" min="0">
                      <small>Cantidad inicial de calificaciones</small>
                  </div>
              </div>

              <button type="submit" class="submit-btn">
                  <i class="fas fa-save"></i> Guardar Producto
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
  const imagenInput = document.getElementById('imagen');
  const imagenesAdicionalesInput = document.getElementById('imagenes_adicionales');
  const previewPrincipal = document.getElementById('preview-principal');
  const previewAdicionales = document.getElementById('preview-adicionales');
  
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
  imagenInput.addEventListener('change', function() {
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
});
</script>
</body>

</html>


