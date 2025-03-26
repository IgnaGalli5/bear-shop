<?php
// Código para verificar los cambios en la base de datos
require_once 'includes/config.php';
require_once 'includes/db.php';

// Seleccionar algunos productos que hayas modificado
$productos = obtenerResultados("
    SELECT id, nombre, precio, precio_costo, multiplicador
    FROM productos
    WHERE id IN (1, 2, 3) /* Reemplaza con los IDs de los productos que modificaste */
    OR nombre LIKE '%oso%' /* O busca por nombre */
");

echo "<h2>Verificación de precios en la base de datos</h2>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Costo</th><th>Multiplicador</th></tr>";

foreach ($productos as $producto) {
    echo "<tr>";
    echo "<td>{$producto['id']}</td>";
    echo "<td>{$producto['nombre']}</td>";
    echo "<td>\${$producto['precio']}</td>";
    echo "<td>\${$producto['precio_costo']}</td>";
    echo "<td>{$producto['multiplicador']}</td>";
    echo "</tr>";
}

echo "</table>";
?>