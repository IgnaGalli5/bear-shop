<?php
/**
 * Archivo de funciones auxiliares para la tienda
 */

/**
 * Sanitiza un string para evitar inyección de código
 * @param string $texto Texto a sanitizar
 * @return string Texto sanitizado
 */
function sanitizar($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

/**
 * Genera un slug a partir de un texto
 * @param string $texto Texto a convertir en slug
 * @return string Slug generado
 */
function generarSlug($texto) {
    // Convertir a minúsculas
    $texto = strtolower($texto);
    
    // Reemplazar caracteres especiales
    $texto = str_replace(
        ['á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ', ' '],
        ['a', 'e', 'i', 'o', 'u', 'u', 'n', '-'],
        $texto
    );
    
    // Eliminar caracteres que no sean alfanuméricos o guiones
    $texto = preg_replace('/[^a-z0-9\-]/', '', $texto);
    
    // Eliminar guiones duplicados
    $texto = preg_replace('/-+/', '-', $texto);
    
    // Eliminar guiones al inicio y al final
    return trim($texto, '-');
}

/**
 * Formatea un precio para mostrar
 * @param float $precio Precio a formatear
 * @return string Precio formateado
 */
function formatearPrecio($precio) {
    return '$' . number_format($precio, 2, ',', '.');
}

/**
 * Verifica si un usuario está autenticado
 * @return bool True si está autenticado, false en caso contrario
 */
function estaAutenticado() {
    return isset($_SESSION['admin_id']);
}

/**
 * Redirecciona a una URL
 * @param string $url URL a la que redireccionar
 */
function redireccionar($url) {
    header("Location: $url");
    exit;
}

/**
 * Muestra un mensaje de error
 * @param string $mensaje Mensaje de error
 * @return string HTML con el mensaje de error
 */
function mostrarError($mensaje) {
    return '<div class="error-message">' . $mensaje . '</div>';
}

/**
 * Muestra un mensaje de éxito
 * @param string $mensaje Mensaje de éxito
 * @return string HTML con el mensaje de éxito
 */
function mostrarExito($mensaje) {
    return '<div class="success-message">' . $mensaje . '</div>';
}

/**
 * Genera un nombre único para un archivo
 * @param string $nombreOriginal Nombre original del archivo
 * @return string Nombre único generado
 */
function generarNombreUnico($nombreOriginal) {
    $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
    return uniqid() . '_' . time() . '.' . $extension;
}

/**
 * Sube una imagen al servidor
 * @param array $archivo Array $_FILES con la información del archivo
 * @param string $directorio Directorio donde se guardará la imagen
 * @return string|false Ruta relativa de la imagen o false si hubo un error
 */
function subirImagen($archivo, $directorio = '../productos/') {
    // Verificar si hay un archivo
    if ($archivo['error'] !== 0) {
        return false;
    }
    
    // Verificar tipo de archivo
    $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!in_array($archivo['type'], $tiposPermitidos)) {
        return false;
    }
    
    // Crear directorio si no existe
    if (!file_exists($directorio)) {
        mkdir($directorio, 0777, true);
    }
    
    // Generar nombre único
    $nombreUnico = generarNombreUnico($archivo['name']);
    $rutaDestino = $directorio . $nombreUnico;
    
    // Mover archivo
    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        // Devolver ruta relativa
        return 'productos/' . $nombreUnico;
    }
    
    return false;
}

/**
 * Obtiene las categorías disponibles
 * @return array Array con las categorías
 */
function obtenerCategorias() {
    return [
        'skincare' => 'Skin Care',
        'maquillaje' => 'Maquillaje',
        'accesorios' => 'Accesorios'
    ];
}

/**
 * Trunca un texto a una longitud determinada
 * @param string $texto Texto a truncar
 * @param int $longitud Longitud máxima
 * @return string Texto truncado
 */
function truncarTexto($texto, $longitud = 100) {
    if (strlen($texto) <= $longitud) {
        return $texto;
    }
    
    return substr($texto, 0, $longitud) . '...';
}