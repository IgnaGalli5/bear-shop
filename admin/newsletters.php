<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Enviar newsletter
$mensaje_envio = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enviar_newsletter'])) {
    $asunto = escapar($_POST['asunto']);
    $contenido = $_POST['contenido']; // No escapamos para permitir HTML
    
    // Obtener suscriptores
    $suscriptores = obtenerResultados("SELECT email, nombre FROM suscriptores");
    
    // Configurar cabeceras
    $cabeceras = "MIME-Version: 1.0" . "\r\n";
    $cabeceras .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $cabeceras .= "From: Bear Shop <info@bearshop.com.ar>" . "\r\n";
    
    // Contador de envíos exitosos
    $enviados = 0;
    
    // Enviar a cada suscriptor
    foreach ($suscriptores as $suscriptor) {
        $email = $suscriptor['email'];
        $nombre = $suscriptor['nombre'] ?: 'Cliente';
        
        // Personalizar contenido
        $contenido_personalizado = str_replace('{NOMBRE}', $nombre, $contenido);
        
        // Enviar email
        if (mail($email, $asunto, $contenido_personalizado, $cabeceras)) {
            $enviados++;
        }
    }
    
    if ($enviados > 0) {
        $mensaje_envio = "Se enviaron $enviados newsletters correctamente.";
    } else {
        $mensaje_envio = "No se pudo enviar ningún newsletter.";
    }
}

// Obtener total de suscriptores
$total_suscriptores = obtenerResultados("SELECT COUNT(*) as total FROM suscriptores")[0]['total'];

// Obtener últimos suscriptores
$ultimos_suscriptores = obtenerResultados("SELECT * FROM suscriptores ORDER BY fecha_suscripcion DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Newsletters - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos base */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333;
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
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
        
        /* Botones y controles */
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
            transition: all 0.2s ease;
        }
        .btn:hover {
            background-color: #e5b78e;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background-color: #945a42;
            color: white;
            padding: 12px 20px;
            font-size: 16px;
        }
        .btn-primary:hover {
            background-color: #7a4a37;
        }
        
        /* Tarjetas y contenedores */
        .page-header {
            margin-bottom: 20px;
        }
        .page-title {
            color: #945a42;
            margin: 0 0 10px 0;
            font-size: 28px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .card-header {
            margin-bottom: 20px;
        }
        .card-title {
            color: #945a42;
            margin: 0 0 10px 0;
            font-size: 22px;
        }
        
        /* Formularios */
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.2s ease;
        }
        .form-control:focus {
            border-color: #945a42;
            outline: none;
            box-shadow: 0 0 0 2px rgba(148, 90, 66, 0.2);
        }
        textarea.form-control {
            min-height: 200px;
            resize: vertical;
        }
        
        /* Mensajes */
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border-left: 4px solid #2e7d32;
        }
        .alert-info {
            background-color: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }
        
        /* Estadísticas */
        .stats-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 30px;
        }
        .stats-card {
            flex: 1;
            min-width: 200px;
            text-align: center;
            padding: 25px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stats-card h3 {
            margin-top: 0;
            color: #945a42;
            font-size: 18px;
        }
        .stats-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
            margin: 15px 0;
        }
        .stats-card .icon {
            font-size: 24px;
            color: #945a42;
            margin-bottom: 15px;
        }
        
        /* Tablas */
        .table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        .table th, 
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .table th {
            background-color: #945a42;
            color: white;
            font-weight: normal;
        }
        .table tr:hover {
            background-color: #f9f9f9;
        }
        .table tr:last-child td {
            border-bottom: none;
        }
        
        /* Etiquetas de plantilla */
        .template-tags {
            background-color: #f9f1e9;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            border-left: 4px solid #945a42;
        }
        .template-tags p {
            margin: 5px 0;
        }
        .template-tags code {
            background-color: #eec8a3;
            padding: 2px 5px;
            border-radius: 3px;
            font-family: monospace;
            color: #945a42;
        }
        
        /* Editor de contenido */
        .editor-toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 4px 4px 0 0;
            border: 1px solid #ddd;
            border-bottom: none;
        }
        .editor-btn {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }
        .editor-btn:hover {
            background-color: #f0f0f0;
            border-color: #ccc;
        }
        .editor-btn i {
            font-size: 14px;
        }
        .editor-preview {
            margin-top: 20px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }
        .editor-preview-title {
            margin-bottom: 10px;
            font-weight: bold;
            color: #945a42;
        }
        
        /* Estilos responsivos */
        @media (max-width: 992px) {
            .container {
                padding: 15px;
            }
            .card {
                padding: 20px;
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
            
            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }
            
            .page-title {
                font-size: 22px;
                text-align: center;
            }
            
            .page-header p {
                text-align: center;
            }
            
            .stats-container {
                flex-direction: column;
                gap: 15px;
            }
            
            .stats-card {
                width: 100%;
                padding: 15px;
            }
            
            .stats-card .value {
                font-size: 28px;
            }
            
            .editor-toolbar {
                justify-content: center;
            }
            
            .submit-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .card-title {
                font-size: 18px;
            }
            
            .template-tags {
                padding: 10px;
            }
            
            .template-tags code {
                font-size: 12px;
            }
            
            .editor-btn {
                padding: 4px 8px;
                font-size: 12px;
            }
            
            .editor-btn i {
                font-size: 12px;
            }
            
            .table th, 
            .table td {
                padding: 8px 10px;
                font-size: 14px;
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
            z-index: 999;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }
        
        @media (max-width: 768px) {
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
            <h2 class="page-title">Gestión de Newsletters</h2>
            <p>Envía newsletters a tus suscriptores y gestiona tu lista de correos.</p>
        </div>
        
        <div class="stats-container">
            <div class="stats-card">
                <div class="icon"><i class="fas fa-users"></i></div>
                <h3>Total de Suscriptores</h3>
                <div class="value"><?php echo $total_suscriptores; ?></div>
                <p>Personas reciben tus newsletters</p>
            </div>
            
            <div class="stats-card">
                <div class="icon"><i class="fas fa-envelope"></i></div>
                <h3>Tasa de Apertura</h3>
                <div class="value">32%</div>
                <p>Promedio de últimas campañas</p>
            </div>
            
            <div class="stats-card">
                <div class="icon"><i class="fas fa-paper-plane"></i></div>
                <h3>Newsletters Enviados</h3>
                <div class="value">12</div>
                <p>En los últimos 30 días</p>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Enviar Newsletter</h3>
                <p>Crea y envía un nuevo newsletter a todos tus suscriptores.</p>
            </div>
            
            <?php if ($mensaje_envio): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $mensaje_envio; ?>
                </div>
            <?php endif; ?>
            
            <div class="template-tags">
                <p><strong>Etiquetas disponibles:</strong></p>
                <p><code>{NOMBRE}</code> - Nombre del suscriptor</p>
                <p>Usa estas etiquetas para personalizar tu mensaje para cada suscriptor.</p>
            </div>
            
            <form method="POST" id="newsletter-form">
                <div class="form-group">
                    <label for="asunto">Asunto del Email *</label>
                    <input type="text" id="asunto" name="asunto" class="form-control" required placeholder="Ej: Nuevos productos de temporada">
                </div>
                
                <div class="editor-toolbar">
                    <button type="button" class="editor-btn" data-command="bold">
                        <i class="fas fa-bold"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="italic">
                        <i class="fas fa-italic"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="underline">
                        <i class="fas fa-underline"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="insertUnorderedList">
                        <i class="fas fa-list-ul"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="insertOrderedList">
                        <i class="fas fa-list-ol"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="createLink">
                        <i class="fas fa-link"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="insertImage">
                        <i class="fas fa-image"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="formatBlock" data-value="h2">
                        <i class="fas fa-heading"></i>
                    </button>
                    <button type="button" class="editor-btn" data-command="foreColor" data-value="#945a42">
                        <i class="fas fa-palette"></i>
                    </button>
                </div>
                
                <div class="form-group">
                    <textarea id="contenido" name="contenido" class="form-control" required placeholder="Escribe el contenido de tu newsletter aquí..."></textarea>
                </div>
                
                <div class="editor-preview" style="display: none;">
                    <div class="editor-preview-title">Vista previa:</div>
                    <div id="preview-content"></div>
                </div>
                
                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="button" id="preview-btn" class="btn">
                        <i class="fas fa-eye"></i> Vista Previa
                    </button>
                    
                    <button type="submit" name="enviar_newsletter" class="btn btn-primary submit-btn">
                        <i class="fas fa-paper-plane"></i> Enviar Newsletter
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Últimos Suscriptores</h3>
                <p>Los últimos 10 suscriptores a tu newsletter.</p>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Nombre</th>
                            <th>Fecha de Suscripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ultimos_suscriptores)): ?>
                            <tr>
                                <td colspan="3" style="text-align: center;">No hay suscriptores registrados</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ultimos_suscriptores as $suscriptor): ?>
                                <tr>
                                    <td><?php echo $suscriptor['email']; ?></td>
                                    <td><?php echo $suscriptor['nombre'] ?: '-'; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($suscriptor['fecha_suscripcion'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="text-align: center; margin-top: 20px;">
                <a href="#" class="btn">
                    <i class="fas fa-download"></i> Exportar Lista Completa
                </a>
            </div>
        </div>
    </div>
    
    <!-- Botón flotante para móvil -->
    <button type="button" class="mobile-fab" id="mobile-submit">
        <i class="fas fa-paper-plane"></i>
    </button>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Editor de texto simple
            const editorButtons = document.querySelectorAll('.editor-btn');
            const contenidoTextarea = document.getElementById('contenido');
            const previewBtn = document.getElementById('preview-btn');
            const previewContent = document.getElementById('preview-content');
            const editorPreview = document.querySelector('.editor-preview');
            const mobileSubmitBtn = document.getElementById('mobile-submit');
            const newsletterForm = document.getElementById('newsletter-form');
            
            // Funcionalidad de los botones del editor
            editorButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const command = this.getAttribute('data-command');
                    const value = this.getAttribute('data-value') || '';
                    
                    if (command === 'createLink') {
                        const url = prompt('Ingresa la URL del enlace:', 'https://');
                        if (url) {
                            // Insertar enlace en la posición del cursor
                            insertAtCursor(contenidoTextarea, `<a href="${url}" target="_blank">texto del enlace</a>`);
                        }
                    } else if (command === 'insertImage') {
                        const url = prompt('Ingresa la URL de la imagen:', 'https://');
                        if (url) {
                            // Insertar imagen en la posición del cursor
                            insertAtCursor(contenidoTextarea, `<img src="${url}" alt="Imagen" style="max-width: 100%; height: auto;">`);
                        }
                    } else if (command === 'bold') {
                        wrapSelectedText(contenidoTextarea, '<strong>', '</strong>');
                    } else if (command === 'italic') {
                        wrapSelectedText(contenidoTextarea, '<em>', '</em>');
                    } else if (command === 'underline') {
                        wrapSelectedText(contenidoTextarea, '<u>', '</u>');
                    } else if (command === 'insertUnorderedList') {
                        wrapSelectedText(contenidoTextarea, '<ul>\n  <li>', '</li>\n</ul>');
                    } else if (command === 'insertOrderedList') {
                        wrapSelectedText(contenidoTextarea, '<ol>\n  <li>', '</li>\n</ol>');
                    } else if (command === 'formatBlock') {
                        wrapSelectedText(contenidoTextarea, `<${value}>`, `</${value}>`);
                    } else if (command === 'foreColor') {
                        wrapSelectedText(contenidoTextarea, `<span style="color:${value}">`, '</span>');
                    }
                });
            });
            
            // Función para insertar texto en la posición del cursor
            function insertAtCursor(textarea, text) {
                const startPos = textarea.selectionStart;
                const endPos = textarea.selectionEnd;
                const scrollTop = textarea.scrollTop;
                
                textarea.value = textarea.value.substring(0, startPos) + text + textarea.value.substring(endPos, textarea.value.length);
                
                textarea.focus();
                textarea.selectionStart = startPos + text.length;
                textarea.selectionEnd = startPos + text.length;
                textarea.scrollTop = scrollTop;
                
                // Actualizar vista previa si está visible
                if (editorPreview.style.display !== 'none') {
                    updatePreview();
                }
            }
            
            // Función para envolver el texto seleccionado
            function wrapSelectedText(textarea, beforeText, afterText) {
                const startPos = textarea.selectionStart;
                const endPos = textarea.selectionEnd;
                const scrollTop = textarea.scrollTop;
                const selectedText = textarea.value.substring(startPos, endPos);
                
                textarea.value = textarea.value.substring(0, startPos) + beforeText + selectedText + afterText + textarea.value.substring(endPos, textarea.value.length);
                
                textarea.focus();
                textarea.selectionStart = startPos + beforeText.length;
                textarea.selectionEnd = startPos + beforeText.length + selectedText.length;
                textarea.scrollTop = scrollTop;
                
                // Actualizar vista previa si está visible
                if (editorPreview.style.display !== 'none') {
                    updatePreview();
                }
            }
            
            // Vista previa del contenido
            previewBtn.addEventListener('click', function() {
                if (editorPreview.style.display === 'none') {
                    updatePreview();
                    editorPreview.style.display = 'block';
                    this.innerHTML = '<i class="fas fa-eye-slash"></i> Ocultar Vista Previa';
                } else {
                    editorPreview.style.display = 'none';
                    this.innerHTML = '<i class="fas fa-eye"></i> Vista Previa';
                }
            });
            
            function updatePreview() {
                // Reemplazar la etiqueta {NOMBRE} con un valor de ejemplo
                let contenido = contenidoTextarea.value.replace(/{NOMBRE}/g, 'Juan Cliente');
                previewContent.innerHTML = contenido;
            }
            
            // Botón flotante para móvil
            mobileSubmitBtn.addEventListener('click', function() {
                // Validar formulario antes de enviar
                if (validateForm()) {
                    newsletterForm.submit();
                }
            });
            
            // Validación simple del formulario
            function validateForm() {
                const asunto = document.getElementById('asunto').value.trim();
                const contenido = contenidoTextarea.value.trim();
                
                if (!asunto) {
                    alert('Por favor, ingresa un asunto para el newsletter.');
                    return false;
                }
                
                if (!contenido) {
                    alert('Por favor, ingresa contenido para el newsletter.');
                    return false;
                }
                
                return true;
            }
            
            // Validar formulario antes de enviar
            newsletterForm.addEventListener('submit', function(event) {
                if (!validateForm()) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>