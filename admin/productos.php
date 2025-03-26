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
        /* Estilos básicos (similares a dashboard.php) */
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
        }

        .user-info span {
            margin-right: 15px;
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
            <div style="display: flex; gap: 10px;">
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
                    <th>Precio Costo</th>
                    <th>Multiplicador</th>
                    <th>Precio Venta</th>
                    <th>Margen</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): 
        // Calcular el margen de ganancia
        $margen = $producto['precio_costo'] > 0 ? (($producto['precio'] / $producto['precio_costo']) - 1) * 100 : 0;
    ?>
        <tr>
            <td><?php echo $producto['id']; ?></td>
            <td>
                <img src="../<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" class="product-image">
            </td>
            <td><?php echo $producto['nombre']; ?></td>
            <td><?php echo $producto['categoria']; ?></td>
            <td><?php echo $producto['precio_costo'] ? '$' . number_format($producto['precio_costo'], 2, ',', '.') : 'No definido'; ?></td>
            <td><?php echo $producto['multiplicador'] ? number_format($producto['multiplicador'], 2, ',', '.') . 'x' : '-'; ?></td>
            <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
            <td><?php echo $producto['precio_costo'] > 0 ? number_format($margen, 0) . '%' : '-'; ?></td>
            <td class="actions">
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
</body>

</html>

