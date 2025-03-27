<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Eliminar producto si se solicita
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    query("DELETE FROM productos WHERE id = $id");
    header('Location: productos.php?mensaje=Producto eliminado correctamente');
    exit;
}

// Procesar búsqueda
$busqueda = isset($_GET['busqueda']) ? escapar($_GET['busqueda']) : '';
$condicion_busqueda = '';
if (!empty($busqueda)) {
    $condicion_busqueda = "WHERE nombre LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%' OR id = '$busqueda'";
}

// Obtener productos (con o sin filtro de búsqueda)
$productos = obtenerResultados("SELECT * FROM productos $condicion_busqueda ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Bear Shop</title>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
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

        /* Estilos para el buscador */
        .search-container {
            margin-bottom: 20px;
            display: flex;
            max-width: 500px;
        }

        .search-container input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            font-size: 16px;
        }

        .search-container button {
            background-color: #945a42;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
        }

        .search-container button:hover {
            background-color: #7a4a37;
        }

        .search-results {
            margin-bottom: 15px;
            font-style: italic;
        }

        /* Estilos responsivos */
        .mobile-hidden {
            display: table-cell;
        }

        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 24px;
            color: white;
            cursor: pointer;
        }

        /* Media queries para responsividad */
        @media screen and (max-width: 992px) {
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
            }
            
            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .mobile-hidden {
                display: none;
            }
        }

        @media screen and (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .logo h1 {
                font-size: 20px;
            }
            
            .btn {
                padding: 6px 12px;
                font-size: 14px;
            }
            
            /* Convertir tabla a formato móvil */
            table thead {
                display: none;
            }
            
            table tbody tr {
                display: block;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 8px;
                padding: 10px;
            }
            
            table tbody td {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 8px 10px;
                text-align: right;
                border-bottom: 1px solid #eee;
            }
            
            table tbody td:last-child {
                border-bottom: none;
            }
            
            table tbody td:before {
                content: attr(data-label);
                font-weight: bold;
                text-align: left;
                margin-right: auto;
            }
            
            .actions {
                flex-direction: column;
                width: 100%;
            }
            
            .actions .btn {
                width: 100%;
                margin-bottom: 5px;
                text-align: center;
            }
            
            .product-image {
                margin: 0 auto;
            }
            
            /* Ajustar buscador */
            .search-container {
                max-width: 100%;
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
        }

        @media screen and (max-width: 768px) {
            .mobile-fab {
                display: flex;
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
                <a href="dashboard.php" class="btn">Dashboard</a>
                <a href="logout.php" class="btn">Cerrar Sesión</a>
            </div>
        </div>
    </header>

    <div class="container">
        <div class="page-header">
            <h2 class="page-title">Gestión de Productos</h2>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="agregar-producto.php" class="btn">
                    <i class="fas fa-plus"></i> Agregar Producto
                </a>
                <a href="importar-productos.php" class="btn">
                    <i class="fas fa-file-import"></i> Importar CSV
                </a>
            </div>
        </div>

        <?php if (isset($_GET['mensaje'])): ?>
            <div class="message">
                <?php echo $_GET['mensaje']; ?>
            </div>
        <?php endif; ?>

        <!-- Buscador de productos -->
        <form method="GET" action="productos.php">
            <div class="search-container">
                <input type="text" name="busqueda" placeholder="Buscar por nombre, categoría o ID..." value="<?php echo $busqueda; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </div>
        </form>

        <?php if (!empty($busqueda)): ?>
            <div class="search-results">
                Mostrando resultados para: "<?php echo $busqueda; ?>"
                <a href="productos.php" class="btn" style="padding: 3px 8px; font-size: 12px;">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Imagen</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th class="mobile-hidden">Precio Costo</th>
                    <th class="mobile-hidden">Multiplicador</th>
                    <th>Precio Venta</th>
                    <th class="mobile-hidden">Margen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): 
                    // Calcular el margen de ganancia
                    $margen = $producto['precio_costo'] > 0 ? (($producto['precio'] / $producto['precio_costo']) - 1) * 100 : 0;
                ?>
                <tr>
                    <td data-label="ID"><?php echo $producto['id']; ?></td>
                    <td data-label="Imagen">
                        <img src="../<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-image">
                    </td>
                    <td data-label="Nombre"><?php echo $producto['nombre']; ?></td>
                    <td data-label="Categoría"><?php echo $producto['categoria']; ?></td>
                    <td data-label="Precio Costo" class="mobile-hidden"><?php echo $producto['precio_costo'] ? '$' . number_format($producto['precio_costo'], 2, ',', '.') : 'No definido'; ?></td>
                    <td data-label="Multiplicador" class="mobile-hidden"><?php echo $producto['multiplicador'] ? number_format($producto['multiplicador'], 2, ',', '.') . 'x' : '-'; ?></td>
                    <td data-label="Precio Venta">$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                    <td data-label="Margen" class="mobile-hidden"><?php echo $producto['precio_costo'] > 0 ? number_format($margen, 0) . '%' : '-'; ?></td>
                    <td data-label="Acciones" class="actions">
                        <a href="editar-producto.php?id=<?php echo $producto['id']; ?>" class="btn">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="productos.php?eliminar=<?php echo $producto['id']; ?>" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                            <i class="fas fa-trash"></i> Eliminar
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (empty($productos)): ?>
                <tr>
                    <td colspan="9" style="text-align: center;">
                        <?php echo !empty($busqueda) ? 'No se encontraron productos que coincidan con la búsqueda.' : 'No hay productos registrados'; ?>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Botón flotante para móvil -->
    <a href="agregar-producto.php" class="mobile-fab">
        <i class="fas fa-plus"></i>
    </a>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Convertir tablas a formato móvil
        function makeTablesResponsive() {
            // Obtener textos de encabezados
            const headerCells = document.querySelectorAll('table thead th');
            const headerTexts = Array.from(headerCells).map(cell => cell.textContent.trim());
            
            // Agregar atributos data-label a celdas
            const bodyCells = document.querySelectorAll('table tbody td');
            bodyCells.forEach((cell, index) => {
                const rowIndex = Math.floor(index / headerTexts.length);
                const columnIndex = index % headerTexts.length;
                cell.setAttribute('data-label', headerTexts[columnIndex]);
            });
        }
        
        // Ejecutar función
        makeTablesResponsive();
    });
    </script>
</body>

</html>