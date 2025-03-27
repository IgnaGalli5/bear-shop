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
        /* Estilos base */
        :root {
            --primary-color: #945a42;
            --primary-dark: #7a4a37;
            --primary-light: #eec8a3;
            --secondary-color: #e5b78e;
            --text-color: #333;
            --error-color: #c62828;
            --error-bg: #ffebee;
            --success-color: #2e7d32;
            --success-bg: #e8f5e9;
            --white: #ffffff;
            --light-gray: #f5f5f5;
            --border-color: #ddd;
            --shadow-color: rgba(0,0,0,0.1);
            --transition-speed: 0.3s;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--light-gray);
            color: var(--text-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: linear-gradient(135deg, #f5f7fa 0%, #e4e8eb 100%);
        }
        
        /* Contenedor de login */
        .login-container {
            background-color: var(--white);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 30px var(--shadow-color);
            width: 100%;
            max-width: 420px;
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            margin: 20px;
        }
        
        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        }
        
        /* Encabezado de login */
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            background-color: var(--primary-color);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 36px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }
        
        .login-header h1 {
            color: var(--primary-color);
            margin: 0 0 5px;
            font-size: 28px;
            letter-spacing: 1px;
        }
        
        .login-header p {
            color: #666;
            font-size: 16px;
        }
        
        /* Formulario */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: var(--text-color);
            font-size: 14px;
            transition: color var(--transition-speed);
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 16px;
            transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
            background-color: var(--light-gray);
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(148, 90, 66, 0.2);
        }
        
        .form-group .input-icon {
            position: absolute;
            left: 15px;
            top: 38px;
            color: #999;
            transition: color var(--transition-speed);
        }
        
        .form-group input:focus + .input-icon,
        .form-group:hover .input-icon {
            color: var(--primary-color);
        }
        
        /* Botón de login */
        .btn {
            background-color: var(--primary-color);
            color: var(--white);
            border: none;
            padding: 14px 20px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            transition: background-color var(--transition-speed), transform var(--transition-speed);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        /* Mensaje de error */
        .error-message {
            background-color: var(--error-bg);
            color: var(--error-color);
            padding: 12px 15px;
            border-radius: 6px;
            margin-bottom: 25px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 14px;
            border-left: 4px solid var(--error-color);
        }
        
        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .login-container {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Estilos responsivos */
        @media (max-width: 480px) {
            .login-container {
                padding: 30px 20px;
                margin: 15px;
            }
            
            .login-logo {
                width: 70px;
                height: 70px;
                font-size: 30px;
            }
            
            .login-header h1 {
                font-size: 24px;
            }
            
            .form-group input {
                padding: 10px 15px 10px 35px;
                font-size: 15px;
            }
            
            .form-group .input-icon {
                left: 12px;
                top: 36px;
                font-size: 14px;
            }
            
            .btn {
                padding: 12px 15px;
            }
        }
        
        /* Modo oscuro */
        @media (prefers-color-scheme: dark) {
            body {
                background-image: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            }
            
            .login-container {
                background-color: #2d3748;
                box-shadow: 0 8px 30px rgba(0,0,0,0.3);
            }
            
            .login-header h1 {
                color: var(--primary-light);
            }
            
            .login-header p {
                color: #cbd5e0;
            }
            
            .form-group label {
                color: #e2e8f0;
            }
            
            .form-group input {
                background-color: #4a5568;
                border-color: #4a5568;
                color: #e2e8f0;
            }
            
            .form-group input:focus {
                border-color: var(--primary-light);
                box-shadow: 0 0 0 3px rgba(238, 200, 163, 0.2);
            }
            
            .form-group .input-icon {
                color: #cbd5e0;
            }
            
            .form-group input:focus + .input-icon,
            .form-group:hover .input-icon {
                color: var(--primary-light);
            }
            
            .btn {
                background-color: var(--primary-color);
            }
            
            .btn:hover {
                background-color: var(--primary-dark);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-paw"></i>
            </div>
            <h1>BEAR SHOP</h1>
            <p>Panel de Administración</p>
        </div>
        
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" id="login-form">
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input type="text" id="usuario" name="usuario" required autocomplete="username">
                <i class="fas fa-user input-icon"></i>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
                <i class="fas fa-lock input-icon"></i>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i>
                Iniciar Sesión
            </button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('login-form');
            const usuarioInput = document.getElementById('usuario');
            const passwordInput = document.getElementById('password');
            const submitButton = form.querySelector('button[type="submit"]');
            
            // Enfocar el primer campo al cargar la página
            usuarioInput.focus();
            
            // Validación básica del formulario
            form.addEventListener('submit', function(event) {
                let isValid = true;
                
                if (!usuarioInput.value.trim()) {
                    isValid = false;
                    showError(usuarioInput, 'Por favor ingresa tu nombre de usuario');
                } else {
                    removeError(usuarioInput);
                }
                
                if (!passwordInput.value) {
                    isValid = false;
                    showError(passwordInput, 'Por favor ingresa tu contraseña');
                } else {
                    removeError(passwordInput);
                }
                
                if (!isValid) {
                    event.preventDefault();
                } else {
                    // Mostrar estado de carga
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Iniciando sesión...';
                    submitButton.disabled = true;
                }
            });
            
            // Funciones para mostrar/ocultar mensajes de error
            function showError(input, message) {
                // Eliminar error existente si hay
                removeError(input);
                
                // Crear elemento de error
                const errorDiv = document.createElement('div');
                errorDiv.className = 'input-error';
                errorDiv.textContent = message;
                errorDiv.style.color = 'var(--error-color)';
                errorDiv.style.fontSize = '12px';
                errorDiv.style.marginTop = '5px';
                
                // Añadir borde rojo al input
                input.style.borderColor = 'var(--error-color)';
                
                // Insertar mensaje después del input
                input.parentNode.appendChild(errorDiv);
            }
            
            function removeError(input) {
                // Restaurar estilo del input
                input.style.borderColor = '';
                
                // Eliminar mensaje de error si existe
                const errorDiv = input.parentNode.querySelector('.input-error');
                if (errorDiv) {
                    errorDiv.remove();
                }
            }
            
            // Limpiar errores al escribir
            usuarioInput.addEventListener('input', function() {
                removeError(this);
            });
            
            passwordInput.addEventListener('input', function() {
                removeError(this);
            });
            
            // Mostrar/ocultar contraseña
            const togglePassword = document.createElement('i');
            togglePassword.className = 'fas fa-eye';
            togglePassword.style.position = 'absolute';
            togglePassword.style.right = '15px';
            togglePassword.style.top = '38px';
            togglePassword.style.cursor = 'pointer';
            togglePassword.style.color = '#999';
            
            passwordInput.parentNode.appendChild(togglePassword);
            
            togglePassword.addEventListener('click', function() {
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    this.className = 'fas fa-eye-slash';
                } else {
                    passwordInput.type = 'password';
                    this.className = 'fas fa-eye';
                }
            });
        });
    </script>
</body>
</html>