document.addEventListener('DOMContentLoaded', function() {
    // Datos de productos
    const products = [
        {
            id: 1,
            name: "ARENCIA - Holy Hyssop Serum 30 50ml",
            price: 28125,
            installments: 3,
            installmentPrice: 9375,
            image: "productos/arencia.jpg"
        },
        {
            id: 2,
            name: "PERIPERA - Ink Black Cara #02",
            price: 31680,
            installments: 3,
            installmentPrice: 10560,
            image: "productos/peripera.jpg"
        },
        {
            id: 3,
            name: "PERIPERA - Ink Mood Matte Stick",
            price: 18090,
            installments: 3,
            installmentPrice: 6030,
            image: "productos/peripera3.jpg"
        },
        {
            id: 4,
            name: "PERIPERA - Speedy Skinny Brow",
            price: 18090,
            installments: 3,
            installmentPrice: 6030,
            image: "productos/peripera4.jpg"
        },
        {
            id: 5,
            name: "ETUDE - Fixing Tint #01 Dusty Beige",
            price: 22500,
            installments: 3,
            installmentPrice: 7500,
            image: "productos/peripera.jpg"
        },
        {
            id: 6,
            name: "ETUDE - Play Color Eyes #Wine Party",
            price: 35700,
            installments: 3,
            installmentPrice: 11900,
            image: "productos/peripera3.jpg"
        },
        {
            id: 7,
            name: "MISSHA - Time Revolution Essence 150ml",
            price: 42300,
            installments: 3,
            installmentPrice: 14100,
            image: "productos/arencia.jpg"
        },
        {
            id: 8,
            name: "MISSHA - Super Aqua Cell Renew Snail Cream",
            price: 29700,
            installments: 3,
            installmentPrice: 9900,
            image: "productos/peripera4.jpg"
        },
        {
            id: 9,
            name: "COSRX - Advanced Snail 96 Mucin Power Essence",
            price: 33000,
            installments: 3,
            installmentPrice: 11000,
            image: "productos/arencia.jpg"
        },
        {
            id: 10,
            name: "COSRX - Low pH Good Morning Gel Cleanser",
            price: 19800,
            installments: 3,
            installmentPrice: 6600,
            image: "productos/peripera.jpg"
        },
        {
            id: 11,
            name: "SOME BY MI - AHA-BHA-PHA 30 Days Miracle Serum",
            price: 27900,
            installments: 3,
            installmentPrice: 9300,
            image: "productos/peripera3.jpg"
        },
        {
            id: 12,
            name: "SOME BY MI - Snail Truecica Miracle Repair Cream",
            price: 31500,
            installments: 3,
            installmentPrice: 10500,
            image: "productos/peripera4.jpg"
        },
        {
            id: 13,
            name: "LANEIGE - Water Sleeping Mask 70ml",
            price: 39600,
            installments: 3,
            installmentPrice: 13200,
            image: "productos/arencia.jpg"
        },
        {
            id: 14,
            name: "LANEIGE - Lip Sleeping Mask 20g",
            price: 24750,
            installments: 3,
            installmentPrice: 8250,
            image: "productos/peripera.jpg"
        },
        {
            id: 15,
            name: "INNISFREE - Green Tea Seed Serum 80ml",
            price: 36300,
            installments: 3,
            installmentPrice: 12100,
            image: "productos/peripera3.jpg"
        },
        {
            id: 16,
            name: "INNISFREE - No Sebum Mineral Powder",
            price: 15900,
            installments: 3,
            installmentPrice: 5300,
            image: "productos/peripera4.jpg"
        },
        {
            id: 17,
            name: "KLAIRS - Supple Preparation Facial Toner",
            price: 26400,
            installments: 3,
            installmentPrice: 8800,
            image: "productos/arencia.jpg"
        },
        {
            id: 18,
            name: "KLAIRS - Midnight Blue Calming Cream",
            price: 32100,
            installments: 3,
            installmentPrice: 10700,
            image: "productos/peripera.jpg"
        },
        {
            id: 19,
            name: "PURITO - Centella Green Level Unscented Sun",
            price: 23100,
            installments: 3,
            installmentPrice: 7700,
            image: "productos/peripera3.jpg"
        },
        {
            id: 20,
            name: "PURITO - Fermented Complex 94 Boosting Essence",
            price: 28800,
            installments: 3,
            installmentPrice: 9600,
            image: "productos/peripera4.jpg"
        }
    ];
    
    // Variables globales
    let cart = [];
    let visibleProducts = 8; // Número inicial de productos visibles
    let filteredProducts = [...products]; // Productos filtrados por búsqueda
    
    // Elementos DOM
    const productGrid = document.getElementById('product-grid');
    const loadMoreBtn = document.getElementById('load-more');
    const searchInput = document.getElementById('product-search');
    const searchBtn = document.getElementById('search-btn');
    const filterResults = document.getElementById('filter-results');
    const searchTerm = document.getElementById('search-term');
    const clearSearchBtn = document.getElementById('clear-search');
    const cartItemsContainer = document.getElementById('cart-items');
    const emptyCartMessage = document.getElementById('empty-cart');
    const cartSummary = document.getElementById('cart-summary');
    const cartSubtotal = document.getElementById('cart-subtotal');
    const cartTotal = document.getElementById('cart-total');
    const checkoutBtn = document.getElementById('checkout-btn');
    const checkoutModal = document.getElementById('checkout-modal');
    const closeModalBtn = document.querySelector('.close');
    const checkoutForm = document.getElementById('checkout-form');
    const cartCount = document.querySelector('.cart-count');
    const menuToggle = document.getElementById('menuToggle');
    const menu = document.querySelector('.menu');
    
    // Funciones
    
    // Formatear precio
    function formatPrice(price) {
        return '$' + price.toLocaleString('es-AR');
    }
    
    // Renderizar productos
    function renderProducts() {
        productGrid.innerHTML = '';
        
        const productsToShow = filteredProducts.slice(0, visibleProducts);
        
        productsToShow.forEach(product => {
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            productCard.innerHTML = `
                <div class="product-image">
                    <img src="${product.image}" alt="${product.name}">
                    <div class="product-overlay">
                        <a href="#" class="btn-small">Ver detalles</a>
                    </div>
                </div>
                <div class="product-info">
                    <h3>${product.name}</h3>
                    <p class="price">${formatPrice(product.price)}<br>
                        ${product.installments} cuotas sin interés de ${formatPrice(product.installmentPrice)}</p>
                    <button class="add-to-cart" data-id="${product.id}">Agregar al carrito</button>
                </div>
            `;
            productGrid.appendChild(productCard);
        });
        
        // Mostrar u ocultar el botón "Ver más"
        if (filteredProducts.length <= visibleProducts) {
            loadMoreBtn.style.display = 'none';
        } else {
            loadMoreBtn.style.display = 'inline-block';
        }
        
        // Agregar eventos a los botones de "Agregar al carrito"
        document.querySelectorAll('.add-to-cart').forEach(button => {
            button.addEventListener('click', function() {
                const productId = parseInt(this.getAttribute('data-id'));
                addToCart(productId);
            });
        });
    }
    
    // Cargar más productos
    function loadMoreProducts() {
        visibleProducts += 4;
        renderProducts();
    }
    
    // Buscar productos
    function searchProducts() {
        const query = searchInput.value.trim().toLowerCase();
        
        if (query === '') {
            filteredProducts = [...products];
            filterResults.style.display = 'none';
        } else {
            filteredProducts = products.filter(product => 
                product.name.toLowerCase().includes(query)
            );
            
            searchTerm.textContent = `"${query}"`;
            filterResults.style.display = 'flex';
        }
        
        visibleProducts = 8;
        renderProducts();
    }
    
    // Limpiar búsqueda
    function clearSearch() {
        searchInput.value = '';
        filteredProducts = [...products];
        filterResults.style.display = 'none';
        visibleProducts = 8;
        renderProducts();
    }
    
    // Agregar producto al carrito
    function addToCart(productId) {
        const product = products.find(p => p.id === productId);
        
        if (!product) return;
        
        const existingItem = cart.find(item => item.id === productId);
        
        if (existingItem) {
            existingItem.quantity += 1;
        } else {
            cart.push({
                ...product,
                quantity: 1
            });
        }
        
        updateCart();
        showNotification(`${product.name} agregado al carrito`);
    }
    
    // Actualizar carrito
    function updateCart() {
        // Actualizar contador de carrito
        const totalItems = cart.reduce((total, item) => total + item.quantity, 0);
        cartCount.textContent = totalItems;
        
        // Si el carrito está vacío
        if (cart.length === 0) {
            emptyCartMessage.style.display = 'block';
            cartSummary.style.display = 'none';
            return;
        }
        
        // Ocultar mensaje de carrito vacío y mostrar resumen
        emptyCartMessage.style.display = 'none';
        cartSummary.style.display = 'block';
        
        // Renderizar items del carrito
        cartItemsContainer.innerHTML = '';
        
        cart.forEach(item => {
            const cartItem = document.createElement('div');
            cartItem.className = 'cart-item';
            cartItem.innerHTML = `
                <div class="cart-item-image">
                    <img src="${item.image}" alt="${item.name}">
                </div>
                <div class="cart-item-details">
                    <h3>${item.name}</h3>
                    <p class="cart-item-price">${formatPrice(item.price)}</p>
                </div>
                <div class="cart-item-actions">
                    <div class="quantity-control">
                        <button class="quantity-btn decrease" data-id="${item.id}">-</button>
                        <input type="number" class="quantity-input" value="${item.quantity}" min="1" data-id="${item.id}">
                        <button class="quantity-btn increase" data-id="${item.id}">+</button>
                    </div>
                    <button class="remove-item" data-id="${item.id}"><i class="fas fa-trash"></i></button>
                </div>
            `;
            cartItemsContainer.appendChild(cartItem);
        });
        
        // Calcular subtotal y total
        const subtotal = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        cartSubtotal.textContent = formatPrice(subtotal);
        cartTotal.textContent = formatPrice(subtotal);
        
        // Agregar eventos a los botones de cantidad
        document.querySelectorAll('.quantity-btn.decrease').forEach(button => {
            button.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                updateItemQuantity(id, 'decrease');
            });
        });
        
        document.querySelectorAll('.quantity-btn.increase').forEach(button => {
            button.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                updateItemQuantity(id, 'increase');
            });
        });
        
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                const id = parseInt(this.getAttribute('data-id'));
                const value = parseInt(this.value);
                
                if (value < 1) {
                    this.value = 1;
                    updateItemQuantity(id, 'set', 1);
                } else {
                    updateItemQuantity(id, 'set', value);
                }
            });
        });
        
        document.querySelectorAll('.remove-item').forEach(button => {
            button.addEventListener('click', function() {
                const id = parseInt(this.getAttribute('data-id'));
                removeFromCart(id);
            });
        });
    }
    
    // Actualizar cantidad de un item
    function updateItemQuantity(id, action, value = null) {
        const itemIndex = cart.findIndex(item => item.id === id);
        
        if (itemIndex === -1) return;
        
        switch (action) {
            case 'increase':
                cart[itemIndex].quantity += 1;
                break;
            case 'decrease':
                if (cart[itemIndex].quantity > 1) {
                    cart[itemIndex].quantity -= 1;
                }
                break;
            case 'set':
                cart[itemIndex].quantity = value;
                break;
        }
        
        updateCart();
    }
    
    // Eliminar producto del carrito
    function removeFromCart(id) {
        const itemIndex = cart.findIndex(item => item.id === id);
        
        if (itemIndex === -1) return;
        
        const removedItem = cart[itemIndex];
        cart.splice(itemIndex, 1);
        
        updateCart();
        showNotification(`${removedItem.name} eliminado del carrito`);
    }
    
    // Mostrar notificación
    function showNotification(message) {
        const notification = document.createElement('div');
        notification.className = 'notification';
        notification.innerHTML = `<p>${message}</p>`;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('fade-out');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 500);
        }, 3000);
    }
    
    // Finalizar compra
    function checkout() {
        if (cart.length === 0) return;
        
        checkoutModal.style.display = 'block';
    }
    
    // Enviar pedido
    function submitOrder(e) {
        e.preventDefault();
        
        const customerName = document.getElementById('customer-name').value;
        const customerPhone = document.getElementById('customer-phone').value;
        const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
        
        // Crear mensaje para WhatsApp
        let message = `*Nuevo Pedido de ${customerName}*\n\n`;
        message += `*Productos:*\n`;
        
        cart.forEach(item => {
            message += `- ${item.name} x${item.quantity} - ${formatPrice(item.price * item.quantity)}\n`;
        });
        
        const total = cart.reduce((total, item) => total + (item.price * item.quantity), 0);
        
        message += `\n*Total:* ${formatPrice(total)}`;
        message += `\n*Método de pago:* ${getPaymentMethodName(paymentMethod)}`;
        
        // Codificar mensaje para URL
        const encodedMessage = encodeURIComponent(message);
        
        // Abrir WhatsApp con el mensaje
        window.open(`https://wa.me/5491122834351?text=${encodedMessage}`, '_blank');
        
        // Cerrar modal y limpiar carrito
        checkoutModal.style.display = 'none';
        cart = [];
        updateCart();
        showNotification('¡Pedido enviado con éxito!');
    }
    
    // Obtener nombre del método de pago
    function getPaymentMethodName(method) {
        switch (method) {
            case 'efectivo':
                return 'Efectivo';
            case 'mercadopago':
                return 'Mercado Pago';
            case 'transferencia':
                return 'Transferencia Bancaria';
            default:
                return method;
        }
    }
    
    // Eventos
    
    // Mobile menu toggle
    if (menuToggle && menu) {
        menuToggle.addEventListener('click', function() {
            menu.classList.toggle('active');
        });
    }
    
    // Cerrar menú al hacer clic fuera
    document.addEventListener('click', function(event) {
        if (!event.target.closest('.menu') && !event.target.closest('#menuToggle') && menu.classList.contains('active')) {
            menu.classList.remove('active');
        }
    });
    
    // Cargar más productos
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function(e) {
            e.preventDefault();
            loadMoreProducts();
        });
    }
    
    // Buscar productos
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            searchProducts();
        });
    }
    
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProducts();
            }
        });
    }
    
    // Limpiar búsqueda
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', clearSearch);
    }
    
    // Finalizar compra
    if (checkoutBtn) {
        checkoutBtn.addEventListener('click', checkout);
    }
    
    // Cerrar modal
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function() {
            checkoutModal.style.display = 'none';
        });
    }
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === checkoutModal) {
            checkoutModal.style.display = 'none';
        }
    });
    
    // Enviar pedido
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', submitOrder);
    }
    
    // Añadir estilos para notificaciones
    const notificationStyle = document.createElement('style');
    notificationStyle.textContent = `
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: rgb(148, 90, 66);
            color: white;
            padding: 15px 25px;
            border-radius: 4px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            animation: slideIn 0.5s ease;
        }
        
        .notification.fade-out {
            animation: fadeOut 0.5s ease forwards;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes fadeOut {
            from {
                opacity: 1;
            }
            to {
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(notificationStyle);
    
    // Inicializar
    renderProducts();
    updateCart();
});