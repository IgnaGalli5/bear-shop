<?php
require_once '../includes/config.php';
require_once '../includes/db.php';

// Verificar si hay sesión iniciada
if (!isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

// Verificar que se ha proporcionado un archivo
if (!isset($_GET['archivo'])) {
    echo "Error: No se especificó un archivo.";
    exit;
}

$archivo = $_GET['archivo'];

// Validar que el archivo está dentro del directorio de logs
if (strpos($archivo, '../logs/') !== 0 || strpos($archivo, '..') !== false) {
    echo "Error: Archivo no válido.";
    exit;
}

// Verificar que el archivo existe
if (!file_exists($archivo)) {
    echo "Error: El archivo no existe.";
    exit;
}

// Leer y mostrar el contenido del archivo
echo file_get_contents($archivo);
?>