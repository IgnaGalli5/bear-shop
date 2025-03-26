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
        /* Estilos similares a los otros archivos admin */
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
        .page-header {
            margin-bottom: 20px;
        }
        .page-title {
            color: #945a42;
            margin: 0 0 10px 0;
        }
        .form-container, .stats-container, .subscribers-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
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
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            box-sizing: border-box;
        }
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
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
        .stats-card {
            text-align: center;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .stats-card h3 {
            margin-top: 0;
            color: #945a42;
        }
        .stats-card .value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
        .template-tags {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .template-tags code {
            background-color: #e0e0e0;
            padding: 2px 5px;
            border-radius: 3px;
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
            <h2 class="page-title">Gestión de Newsletters</h2>
            <p>Envía newsletters a tus suscriptores y gestiona tu lista de correos.</p>
        </div>
        
        <div class="stats-container">
            <div class="stats-card">
                <h3>Total de Suscriptores</h3>
                <div class="value"><?php echo $total_suscriptores; ?></div>
            </div>
        </div>
        
        <div class="form-container">
            <h3>Enviar Newsletter</h3>
            
            <?php if ($mensaje_envio): ?>
                <div class="success-message">
                    <?php echo $mensaje_envio; ?>
                </div>
            <?php endif; ?>
            
            <div class="template-tags">
                <p><strong>Etiquetas disponibles:</strong></p>
                <p><code>{NOMBRE}</code> - Nombre del suscriptor</p>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label for="asunto">Asunto del Email *</label>
                    <input type="text" id="asunto" name="asunto" required>
                </div>
                
                <div class="form-group">
                    <label for="contenido">Contenido del Email (HTML permitido) *</label>
                    <textarea id="contenido" name="contenido" required></textarea>
                </div>
                
                <button type="submit" name="enviar_newsletter" class="submit-btn">
                    <i class="fas fa-paper-plane"></i> Enviar Newsletter
                </button>
            </form>
        </div>
        
        <div class="subscribers-container">
            <h3>Últimos Suscriptores</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Nombre</th>
                        <th>Fecha de Suscripción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ultimos_suscriptores as $suscriptor): ?>
                    <tr>
                        <td><?php echo $suscriptor['email']; ?></td>
                        <td><?php echo $suscriptor['nombre'] ?: '-'; ?></td>
                        <td><?php echo $suscriptor['fecha_suscripcion']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if (empty($ultimos_suscriptores)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center;">No hay suscriptores registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>