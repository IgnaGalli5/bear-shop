<?php
// Incluir archivos de configuración
require_once 'includes/config.php';
require_once 'includes/db.php';

// Modificar la consulta SQL para obtener productos con sus promociones
$productos_destacados = obtenerResultados("
    SELECT p.*, 
           CASE 
               WHEN pr.id IS NOT NULL AND pr.activa = 1 
                    AND pr.fecha_inicio <= CURDATE() 
                    AND pr.fecha_fin >= CURDATE() 
               THEN 
                   CASE 
                       WHEN pr.tipo = 'porcentaje' THEN p.precio - (p.precio * pr.valor / 100)
                       WHEN pr.tipo = 'monto_fijo' THEN p.precio - pr.valor
                       ELSE p.precio_promocion
                   END
               ELSE p.precio_promocion
           END as precio_promocion,
           pr.nombre as nombre_promocion,
           pr.tipo as tipo_promocion,
           pr.valor as valor_promocion
    FROM productos p
    LEFT JOIN promociones pr ON p.promocion_id = pr.id
    ORDER BY p.id DESC 
    LIMIT 8
");

// Obtener categorías
$categorias = obtenerResultados("SELECT DISTINCT categoria FROM productos WHERE categoria IS NOT NULL AND categoria != '' GROUP BY categoria ORDER BY categoria");

// Obtener promociones activas para el slider
$promociones = obtenerResultados("SELECT * FROM promociones WHERE activa = 1 AND fecha_inicio <= CURDATE() AND fecha_fin >= CURDATE() ORDER BY destacada DESC LIMIT 4");

// Función para formatear precio
function formatearPrecioFront($precio) {
    return number_format($precio, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <!-- Configuración básica del documento -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bear.Shop - Tienda Online</title>
    <!-- Enlaces a hojas de estilo -->
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.cdnfonts.com/css/bogart" rel="stylesheet">
    <link rel="shortcut icon" href="img/icon.png" />
    <style>
        .discount-label {
            color: #e74c3c;
            font-weight: bold;
            font-size: 0.85em;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Encabezado del sitio con navegación -->
    <header>
        <div class="container">
            <!-- Logo del sitio -->
            <div class="logo">
                <h1>BEAR</h1>
            </div>
            <!-- Menú de navegación principal -->
            <nav>
                <ul class="menu">
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#productos">Productos</a></li>
                    <li><a href="#carrito">Carrito</a></li>
                </ul>
            </nav>
            <!-- Iconos de funcionalidad (búsqueda, usuario, carrito) -->
            <div class="icons">
                <a href="#" class="icon" id="search-icon"><i class="fas fa-search"></i></a>
                <a href="#" class="icon"><i class="fas fa-user"></i></a>
                <a href="#carrito" class="icon" id="cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                    <span class="cart-count">0</span>
                </a>
                <!-- Botón de menú para móviles -->
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    <!-- Sección de novedades/promociones con imágenes rotativas -->
    <section class="news-slider">
        <div class="container">
            <div class="slider-container">
                <div class="slider" id="news-slider">
                    <?php if (count($promociones) > 0): ?>
                        <?php foreach ($promociones as $index => $promo): ?>
                            <div class="slide <?php echo $index === 0 ? 'active' : ''; ?>">
                                <?php 
                                // Determinar la imagen a mostrar
                                $imagen = !empty($promo['imagen']) ? $promo['imagen'] : 'promos/banner' . ($index + 1) . '.jpg';
                                ?>
                                <div class="slide-content">
                                    <img src="<?php echo $imagen; ?>" alt="<?php echo $promo['nombre']; ?>">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Imágenes por defecto si no hay promociones -->
                        <div class="slide active">
                            <div class="slide-content">
                                <img src="promos/descuentos.jpeg" alt="descuentos">
                            </div>
                        </div>
                        <div class="slide">
                            <div class="slide-content">
                                <img src="promos/envios.jpeg" alt="Envios">
                            </div>
                        </div>
                        <div class="slide">
                            <div class="slide-content">
                                <img src="promos/regalos.jpeg" alt="Regalos">
                            </div>
                        </div>
                        <div class="slide">
                            <div class="slide-content">
                                <img src="promos/banner4.jpg" alt="Oferta especial">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Indicadores de posición del slider -->
                <div class="slider-indicators" id="slider-indicators">
                    <?php 
                    $total_slides = count($promociones) > 0 ? count($promociones) : 4;
                    for ($i = 0; $i < $total_slides; $i++): 
                    ?>
                        <span class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" data-index="<?php echo $i; ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Sección hero con imagen principal y buscador -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
               <img src="img/logo-png.png" alt="bear-shop" class="logo">
               
               <!-- Buscador de productos -->
               <div class="search-container">
                   <input type="text" id="product-search" placeholder="Buscar productos...">
                   <button id="search-btn"><i class="fas fa-search"></i></button>
               </div>
               
                <a href="#productos" class="btn">Ver productos</a>
            </div>
        </div>
    </section>

    <!-- Sección de productos -->
    <section id="productos" class="products">
        <div class="container">
            <h2 class="section-title">Nuestros Productos</h2>
            
            <!-- Filtros de búsqueda -->
            <div class="filter-results" id="filter-results" style="display: none;">
                <h3>Resultados de búsqueda: <span id="search-term"></span></h3>
                <button id="clear-search" class="btn-small">Limpiar búsqueda</button>
            </div>
            
            <!-- Contenedor de productos -->
            <div class="product-grid" id="product-grid">
                <?php if (count($productos_destacados) > 0): ?>
                    <?php foreach ($productos_destacados as $producto): ?>
                        <div class="product-card" data-id="<?php echo $producto['id']; ?>" data-category="<?php echo $producto['categoria']; ?>">
                            <div class="product-image">
                                <img src="<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>">
                                <?php if (isset($producto['precio_promocion']) && $producto['precio_promocion'] > 0): ?>
                                    <span class="discount-badge">Oferta</span>
                                <?php endif; ?>
                            </div>
                            <div class="product-info">
                                <h3><?php echo $producto['nombre']; ?></h3>
                                <div class="product-price">
                                    <?php if (isset($producto['precio_promocion']) && $producto['precio_promocion'] > 0): ?>
                                        <p class="price"><span class="original-price">$<?php echo formatearPrecioFront($producto['precio']); ?></span> $<?php echo formatearPrecioFront($producto['precio_promocion']); ?></p>
                                        <?php if (isset($producto['nombre_promocion']) && !empty($producto['nombre_promocion'])): ?>
                                            <p class="discount-label"><?php echo $producto['nombre_promocion']; ?></p>
                                        <?php else: ?>
                                            <p class="discount-label">¡OFERTA!</p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="price">$<?php echo formatearPrecioFront($producto['precio']); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div class="product-actions">
                                    <button class="btn-small view-product" data-id="<?php echo $producto['id']; ?>">Ver detalles</button>
                                    <button class="btn-small add-to-cart" data-id="<?php echo $producto['id']; ?>">Agregar</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <!-- Los productos adicionales se cargarán dinámicamente con JavaScript -->
            </div>
            <div class="view-more">
                <a href="#" class="btn" id="load-more">Ver más productos</a>
            </div>
        </div>
    </section>

    <!-- Sección de categorías de productos -->
    <section id="categorias" class="categories">
        <div class="container">
            <h2 class="section-title">Categorías</h2>
            <div class="category-grid">
                <?php if (count($categorias) > 0): ?>
                    <?php foreach ($categorias as $index => $cat): 
                        // Limitar a 3 categorías para mostrar
                        if ($index >= 3) break;
                        
                        // Determinar la imagen a mostrar
                        $cat_img = '';
                        switch(strtolower($cat['categoria'])) {
                            case 'skincare':
                                $cat_img = 'skincare.jpeg';
                                break;
                            case 'maquillaje':
                                $cat_img = 'maquillaje.jpeg';
                                break;
                            case 'accesorios':
                                $cat_img = 'accesorios.jpeg';
                                break;
                            default:
                                $cat_img = 'categoria' . ($index + 1) . '.jpeg';
                        }
                    ?>
                        <div class="category-card" data-category="<?php echo $cat['categoria']; ?>">
                            <img src="<?php echo $cat_img; ?>" alt="<?php echo ucfirst($cat['categoria']); ?>">
                            <h3><?php echo ucfirst($cat['categoria']); ?></h3>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Categorías por defecto si no hay en la base de datos -->
                    <div class="category-card" data-category="skincare">
                        <img src="skincare.jpeg" alt="Categoría 1">
                        <h3>Skin Care</h3>
                    </div>
                    <div class="category-card" data-category="maquillaje">
                        <img src="maquillaje.jpeg" alt="Categoría 2">
                        <h3>Maquillaje</h3>
                    </div>
                    <div class="category-card" data-category="accesorios">
                        <img src="accesorios.jpeg" alt="Categoría 3">
                        <h3>Accesorios</h3>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Sección de carrito de compras -->
    <section id="carrito" class="cart-section">
        <div class="container">
            <h2 class="section-title">Tu Carrito de Compras</h2>
            <div class="cart-content">
                <!-- Contenedor de items del carrito -->
                <div class="cart-items" id="cart-items">
                    <!-- Los items del carrito se cargarán dinámicamente -->
                    <div class="empty-cart" id="empty-cart">
                        <i class="fas fa-shopping-cart fa-4x"></i>
                        <p>Tu carrito está vacío</p>
                        <a href="#productos" class="btn">Ver productos</a>
                    </div>
                </div>
                
                <!-- Resumen del carrito -->
                <div class="cart-summary" id="cart-summary" style="display: none;">
                    <h3>Resumen de compra</h3>
                    <div class="summary-item">
                        <span>Subtotal:</span>
                        <span id="cart-subtotal">$0.00</span>
                    </div>
                    <div class="summary-item">
                        <span>Envío:</span>
                        <span>Gratis</span>
                    </div>
                    <div class="summary-item total">
                        <span>Total:</span>
                        <span id="cart-total">$0.00</span>
                    </div>
                    
                    <!-- Opciones de pago -->
                    <div class="payment-methods">
                        <h4>Método de pago:</h4>
                        <div class="payment-options">
                            <label>
                                <input type="radio" name="payment" value="efectivo" checked id="payment-efectivo">
                                <span>Efectivo (10% de descuento)</span>
                            </label>
                            <label>
                                <input type="radio" name="payment" value="mercadopago" id="payment-mercadopago">
                                <span>Mercado Pago</span>
                            </label>
                            <label>
                                <input type="radio" name="payment" value="transferencia" id="payment-transferencia">
                                <span>Transferencia Bancaria</span>
                            </label>
                        </div>
                    </div>
                    
                    <button id="checkout-btn" class="btn">Finalizar compra</button>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal para finalizar compra -->
    <div id="checkout-modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Finalizar Compra</h2>
            
            <img src="img/medios-pagos.jpg" alt="Medios de pago" class="checkout-payment-img">
            
            <form id="checkout-form">
                <div class="form-group">
                    <label for="customer-name">Nombre completo</label>
                    <input type="text" id="customer-name" required>
                </div>
                <div class="form-group">
                    <label for="customer-email">Email</label>
                    <input type="email" id="customer-email" required>
                </div>
                <div class="form-group">
                    <label for="customer-cel">Telefono</label>
                    <input type="celphone" id="customer-celphone" required>
                </div>
                <div class="form-group">
                    <label for="customer-adress">Dirección</label>
                    <input type="adress" id="customer-adress" required>
                </div>
                <div class="form-group">
                    <label for="customer-cp">Codigo Postal</label>
                    <input type="cp" id="customer-cp" required>
                </div>
                <button type="submit" class="btn">Enviar pedido</button>
            </form>
        </div>
    </div>

    <!-- Modal para detalles de producto -->
    <div id="product-modal" class="modal">
        <div class="modal-content product-detail-modal">
            <span class="close">&times;</span>
            <div class="product-detail-content">
                <div class="product-detail-image">
                    <!-- La imagen se cargará dinámicamente -->
                </div>
                <div class="product-detail-info">
                    <h2 id="modal-product-name"></h2>
                    <p class="modal-product-price"></p>
                    <div class="product-rating">
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star-half-alt"></i>
                        </div>
                        <span class="rating-count"></span>
                    </div>
                    <div class="product-description">
                        <h3>Descripción</h3>
                        <p id="modal-product-description"></p>
                    </div>
                    <div class="product-features">
                        <h3>Características</h3>
                        <ul id="modal-product-features">
                            <!-- Las características se cargarán dinámicamente -->
                        </ul>
                    </div>
                    <div class="product-usage">
                        <h3>Modo de uso</h3>
                        <p id="modal-product-usage"></p>
                    </div>
                    <div class="product-quantity">
                        <h3>Cantidad</h3>
                        <div class="quantity-control">
                            <button class="quantity-btn decrease" id="modal-quantity-decrease">-</button>
                            <input type="number" class="quantity-input" id="modal-quantity" value="1" min="1">
                            <button class="quantity-btn increase" id="modal-quantity-increase">+</button>
                        </div>
                    </div>
                    <button class="btn add-to-cart-modal" id="modal-add-to-cart">Agregar al carrito</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie de página -->
<footer>
    <div class="container">
        <div class="footer-content">
            <!-- Logo del footer -->
            <div class="footer-logo">
                <h2>BEAR</h2>
                <!-- Imágenes de medios de pago y envíos -->
                <div class="payment-shipping-methods">
                    <div class="payment-methods-img">
                        <h4>Medios de pago</h4>
                        <img src="img/medios1.png" alt="Medios de pago" class="desktop-payment">
                        <img src="img/medios2.png" alt="Medios de pago" class="mobile-payment">
                    </div>
                    <div class="shipping-methods-img">
                        <h4>Envíos</h4>
                        <img src="img/envio.png" alt="Métodos de envío">
                    </div>
                </div>
            </div>
            <!-- Enlaces rápidos -->
            <div class="footer-links">
                <h3>Enlaces rápidos</h3>
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#productos">Productos</a></li>
                    <li><a href="#carrito">Carrito</a></li>
                </ul>
            </div>
            <!-- Formulario de newsletter -->
            <div class="newsletter">
                <h3>Suscríbete a nuestro Newsletter</h3>
                <p>Recibe nuestras promociones y novedades</p>
                <form id="newsletter-form">
                    <input type="email" id="newsletter-email" placeholder="Tu email" required>
                    <button type="submit">Suscribirse</button>
                </form>
                <div id="newsletter-message"></div>
            </div>
        </div>
        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> BEAR. Todos los derechos reservados.</p>
            <!-- <p>Creado por <a href="https://ignacode.com" target="_blank" style="color: #945a42; text-decoration: none; font-weight: bold;">Igna.Code</a></p>-->
             <p>Creado por Igna.Code</p>
        </div>
    </div>
</footer>


    <!-- Script principal -->
    <script src="script.js"></script>
    <script>
        // Pasar datos de productos a JavaScript
        const initialProducts = <?php echo json_encode($productos_destacados); ?>;
        
        document.getElementById('newsletter-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = document.getElementById('newsletter-email').value;
            const messageDiv = document.getElementById('newsletter-message');
            
            // Enviar solicitud
            fetch('api/suscribir.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                if (data.exito) {
                    messageDiv.innerHTML = '<p class="success">' + data.mensaje + '</p>';
                    document.getElementById('newsletter-email').value = '';
                } else {
                    messageDiv.innerHTML = '<p class="error">' + data.mensaje + '</p>';
                }
            })
            .catch(error => {
                messageDiv.innerHTML = '<p class="error">Error al procesar la solicitud</p>';
                console.error('Error:', error);
            });
        });

        // Aplicar descuento por pago en efectivo
        const paymentEfectivo = document.getElementById('payment-efectivo');
        const paymentOthers = document.querySelectorAll('input[name="payment"]:not(#payment-efectivo)');
        const cartSubtotalElement = document.getElementById('cart-subtotal');
        const cartTotalElement = document.getElementById('cart-total');

        // Función para actualizar el total con o sin descuento
        function updateTotalWithDiscount() {
            // Obtener el subtotal actual sin formato
            const subtotalText = cartSubtotalElement.textContent.replace('$', '').replace('.', '');
            const subtotal = parseFloat(subtotalText.replace(',', '.'));
            
            if (paymentEfectivo.checked) {
                // Aplicar 10% de descuento
                const discount = subtotal * 0.1;
                const totalWithDiscount = subtotal - discount;
                
                // Mostrar el total con descuento
                cartTotalElement.innerHTML = '$' + formatPrice(totalWithDiscount) + 
                    ' <small class="discount-label">(10% descuento aplicado)</small>';
            } else {
                // Mostrar el total sin descuento
                cartTotalElement.innerHTML = '$' + formatPrice(subtotal);
            }
        }

        // Formatear precio para mostrar
        function formatPrice(price) {
            return price.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Agregar event listeners a los radio buttons
        paymentEfectivo.addEventListener('change', updateTotalWithDiscount);
        paymentOthers.forEach(radio => {
            radio.addEventListener('change', updateTotalWithDiscount);
        });

        // Actualizar cuando se carga la página
        document.addEventListener('DOMContentLoaded', function() {
            // Observar cambios en el subtotal (cuando se agregan/quitan productos)
            const observer = new MutationObserver(updateTotalWithDiscount);
            observer.observe(cartSubtotalElement, { childList: true, characterData: true, subtree: true });
            
            // Inicializar
            updateTotalWithDiscount();
        });
    </script>
</body>
</html>

