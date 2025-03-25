<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

$error = '';

// Verificar si ya hay sesión iniciada
if (isset($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

// Procesar formulario de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = escapar($_POST['usuario']);
    $password = $_POST['password'];
    
    // Buscar usuario en la base de datos
    $sql = "SELECT * FROM usuarios WHERE usuario = '$usuario' LIMIT 1";
    $resultado = query($sql);
    
    if ($resultado->num_rows > 0) {
        $usuario_db = $resultado->fetch_assoc();
        
        // Verificar contraseña (en producción usar password_verify)
        if ($password === 'bear') { // Simplificado para el ejemplo
            // Iniciar sesión
            $_SESSION['admin_id'] = $usuario_db['id'];
            $_SESSION['admin_nombre'] = $usuario_db['nombre'];
            
            // Redireccionar al dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Contraseña incorrecta';
        }
    } else {
        $error = 'Usuario no encontrado';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Bear Shop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Estilos básicos */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #945a42;
            margin: 0;
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
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            background-color: #945a42;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
        }
        .btn:hover {
            background-color: #7a4a37;
        }
        .error-message {
            background-color: #ffebee;
            color: #c62828;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>BEAR SHOP</h1>
            <p>Panel de Administración</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>