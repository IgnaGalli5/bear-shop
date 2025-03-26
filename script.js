document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  let cart = [] // Carrito de compras
  let visibleProducts = 8 // Número inicial de productos visibles
  let products = [] // Productos cargados desde la API
  let filteredProducts = [] // Productos filtrados por búsqueda
  let currentProductId = null // ID del producto actualmente mostrado en el modal
  let currentSlide = 0 // Índice de la diapositiva actual en el slider de novedades

  // Elementos DOM - Referencias a elementos HTML para manipularlos con JavaScript
  const productGrid = document.getElementById("product-grid")
  const loadMoreBtn = document.getElementById("load-more")
  const searchInput = document.getElementById("product-search")
  const searchBtn = document.getElementById("search-btn")
  const filterResults = document.getElementById("filter-results")
  const searchTerm = document.getElementById("search-term")
  const clearSearchBtn = document.getElementById("clear-search")
  const cartItemsContainer = document.getElementById("cart-items")
  const emptyCartMessage = document.getElementById("empty-cart")
  const cartSummary = document.getElementById("cart-summary")
  const cartSubtotal = document.getElementById("cart-subtotal")
  const cartTotal = document.getElementById("cart-total")
  const checkoutBtn = document.getElementById("checkout-btn")
  const checkoutModal = document.getElementById("checkout-modal")
  const closeModalBtn = document.querySelector(".close")
  const checkoutForm = document.getElementById("checkout-form")
  const cartCount = document.querySelector(".cart-count")
  const menuToggle = document.getElementById("menuToggle")
  const menu = document.querySelector(".menu")

  // Elementos del slider de novedades
  const slider = document.getElementById("news-slider")
  const slides = slider ? slider.querySelectorAll(".slide") : []
  const indicators = document.getElementById("slider-indicators")
  const indicatorDots = indicators ? indicators.querySelectorAll(".indicator") : []

  // Elementos del modal de producto
  const productModal = document.getElementById("product-modal")
  const productModalClose = productModal ? productModal.querySelector(".close") : null
  const modalProductName = document.getElementById("modal-product-name")
  const modalProductPrice = document.querySelector(".modal-product-price")
  const modalProductDescription = document.getElementById("modal-product-description")
  const modalProductFeatures = document.getElementById("modal-product-features")
  const modalProductUsage = document.getElementById("modal-product-usage")
  const modalProductImage = document.querySelector(".product-detail-image")
  const modalRatingCount = document.querySelector(".rating-count")
  const modalQuantity = document.getElementById("modal-quantity")
  const modalQuantityDecrease = document.getElementById("modal-quantity-decrease")
  const modalQuantityIncrease = document.getElementById("modal-quantity-increase")
  const modalAddToCart = document.getElementById("modal-add-to-cart")

  // Funciones
  

  // Formatear precio: convierte un número a formato de moneda
  function formatPrice(price) {
    return "$" + price.toLocaleString("es-AR")
  }

  // Renderizar productos: muestra los productos en la grilla areglado recien
  function renderProducts() {
    if (!productGrid) return;
  
    productGrid.innerHTML = ""
  
    const productsToShow = filteredProducts.slice(0, visibleProducts)
  
    productsToShow.forEach((product) => {
      const productCard = document.createElement("div")
      productCard.className = "product-card"
      if (product.onSale) {
        productCard.classList.add("on-sale")
      }
      productCard.setAttribute("data-id", product.id)
      
      // Crear HTML para el precio (normal o promocional)
      let priceHTML = `<p class="price">${formatPrice(product.price)}</p>`;
      if (product.onSale && product.originalPrice) {
        priceHTML = `
          <p class="price">
            <span class="original-price">${formatPrice(product.originalPrice)}</span>
            <span class="sale-price">${formatPrice(product.price)}</span>
          </p>
          <p class="sale-badge">¡OFERTA!</p>
        `;
      }
      
      productCard.innerHTML = `
                  <div class="product-image">
                      <img src="${product.image}" alt="${product.name}">
                      <div class="product-overlay">
                          <a href="#" class="btn-small">Ver detalles</a>
                      </div>
                  </div>
                  <div class="product-info">
                      <h3>${product.name}</h3>
                      ${priceHTML}
                      <button class="add-to-cart" data-id="${product.id}">Agregar al carrito</button>
                  </div>
              `
      productGrid.appendChild(productCard)
    })

    // Mostrar u ocultar el botón "Ver más" según la cantidad de productos
    if (loadMoreBtn) {
      if (filteredProducts.length <= visibleProducts) {
        loadMoreBtn.style.display = "none"
      } else {
        loadMoreBtn.style.display = "inline-block"
      }
    }

    // Agregar eventos a los botones de "Agregar al carrito"
    document.querySelectorAll(".add-to-cart").forEach((button) => {
      button.addEventListener("click", function (e) {
        e.stopPropagation() // Evitar que se abra el modal al hacer clic en el botón
        const productId = Number.parseInt(this.getAttribute("data-id"))
        addToCart(productId)
      })
    })

    // Agregar evento para abrir el modal al hacer clic en la tarjeta de producto
    document.querySelectorAll(".product-card").forEach((card) => {
      card.addEventListener("click", function () {
        const productId = Number.parseInt(this.getAttribute("data-id"))
        openProductModal(productId)
      })
    })
  }
  

  // Abrir modal de producto: muestra los detalles de un producto
  function openProductModal(productId) {
    if (!productModal) return;
    
    const product = products.find((p) => p.id === productId)
  
    if (!product) return
  
    currentProductId = productId
  
    // Actualizar contenido del modal
    if (modalProductName) modalProductName.textContent = product.name
    if (modalProductPrice) modalProductPrice.textContent = formatPrice(product.price)
    if (modalProductDescription) modalProductDescription.textContent = product.description
    if (modalProductUsage) modalProductUsage.textContent = product.usage
    if (modalRatingCount) modalRatingCount.textContent = `(${product.ratingCount} reseñas)`

    // Actualizar estrellas según la calificación
    const stars = productModal.querySelectorAll(".stars i")
    const fullStars = Math.floor(product.rating)
    const hasHalfStar = product.rating % 1 >= 0.5

    stars.forEach((star, index) => {
      star.className = "fas fa-star" // Resetear todas a estrellas completas

      if (index >= fullStars) {
        if (index === fullStars && hasHalfStar) {
          star.className = "fas fa-star-half-alt" // Media estrella
        } else {
          star.className = "far fa-star" // Estrella vacía
        }
      }
    })

    // Actualizar características
    if (modalProductFeatures) {
      modalProductFeatures.innerHTML = ""
      product.features.forEach((feature) => {
        const li = document.createElement("li")
        li.textContent = feature
        modalProductFeatures.appendChild(li)
      })
    }

    // Actualizar imagen
    if (modalProductImage) modalProductImage.innerHTML = `<img src="${product.image}" alt="${product.name}">`

    // Resetear cantidad
    if (modalQuantity) modalQuantity.value = 1

    // Mostrar modal
    productModal.style.display = "block"
    
    // Mostrar botón flotante en móviles si la pantalla es pequeña
    if (window.innerWidth <= 768 && mobileCloseBtn) {
      mobileCloseBtn.style.display = "flex"
    }
  }

  // Cargar más productos: aumenta la cantidad de productos visibles
  function loadMoreProducts() {
    visibleProducts += 4
    renderProducts()
  }

  // Buscar productos: filtra los productos según el término de búsqueda
  function searchProducts() {
    if (!searchInput) return;
    
    const query = searchInput.value.trim().toLowerCase()

    if (query === "") {
      filteredProducts = [...products]
      if (filterResults) filterResults.style.display = "none"
    } else {
      filteredProducts = products.filter(
        (product) =>
          product.name.toLowerCase().includes(query) ||
          product.category.toLowerCase().includes(query) ||
          product.description.toLowerCase().includes(query),
      )

      if (searchTerm) searchTerm.textContent = `"${query}"`
      if (filterResults) filterResults.style.display = "flex"
    }

    visibleProducts = 8
    renderProducts()
  }

  // Limpiar búsqueda: restablece los productos mostrados
  function clearSearch() {
    if (searchInput) searchInput.value = ""
    filteredProducts = [...products]
    if (filterResults) filterResults.style.display = "none"
    visibleProducts = 8
    renderProducts()
  }

  // Agregar producto al carrito
  function addToCart(productId, quantity = 1) {
    const product = products.find((p) => p.id === productId)

    if (!product) return

    const existingItem = cart.find((item) => item.id === productId)

    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      cart.push({
        ...product,
        quantity: quantity,
      })
    }

    saveCart()
    updateCart()
    showNotification(`${product.name} agregado al carrito`)
  }

  // Actualizar carrito: actualiza la visualización del carrito y los totales
  function updateCart() {
    // Actualizar contador de carrito
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0)
    if (cartCount) cartCount.textContent = totalItems

    // Si el carrito está vacío
    if (cart.length === 0) {
      if (emptyCartMessage) emptyCartMessage.style.display = "block"
      if (cartSummary) cartSummary.style.display = "none"
      return
    }

    // Ocultar mensaje de carrito vacío y mostrar resumen
    if (emptyCartMessage) emptyCartMessage.style.display = "none"
    if (cartSummary) cartSummary.style.display = "block"

    // Renderizar items del carrito
    if (!cartItemsContainer) return;
    
    cartItemsContainer.innerHTML = ""

    cart.forEach((item) => {
      const cartItem = document.createElement("div")
      cartItem.className = "cart-item"
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
              `
      cartItemsContainer.appendChild(cartItem)
    })

    // Calcular subtotal y total
    const subtotal = cart.reduce((total, item) => total + item.price * item.quantity, 0)
    if (cartSubtotal) cartSubtotal.textContent = formatPrice(subtotal)
    if (cartTotal) cartTotal.textContent = formatPrice(subtotal)

    // Agregar eventos a los botones de cantidad
    document.querySelectorAll(".quantity-btn.decrease").forEach((button) => {
      button.addEventListener("click", function () {
        const id = Number.parseInt(this.getAttribute("data-id"))
        updateItemQuantity(id, "decrease")
      })
    })

    document.querySelectorAll(".quantity-btn.increase").forEach((button) => {
      button.addEventListener("click", function () {
        const id = Number.parseInt(this.getAttribute("data-id"))
        updateItemQuantity(id, "increase")
      })
    })

    document.querySelectorAll(".quantity-input").forEach((input) => {
      input.addEventListener("change", function () {
        const id = Number.parseInt(this.getAttribute("data-id"))
        const value = Number.parseInt(this.value)

        if (value < 1) {
          this.value = 1
          updateItemQuantity(id, "set", 1)
        } else {
          updateItemQuantity(id, "set", value)
        }
      })
    })

    document.querySelectorAll(".remove-item").forEach((button) => {
      button.addEventListener("click", function () {
        const id = Number.parseInt(this.getAttribute("data-id"))
        removeFromCart(id)
      })
    })
  }

  // Actualizar cantidad de un item en el carrito
  function updateItemQuantity(id, action, value = null) {
    const itemIndex = cart.findIndex((item) => item.id === id)
    if (itemIndex === -1) return

    switch (action) {
      case "increase":
        cart[itemIndex].quantity += 1
        break
      case "decrease":
        if (cart[itemIndex].quantity > 1) {
          cart[itemIndex].quantity -= 1
        }
        break
      case "set":
        cart[itemIndex].quantity = value
        break
    }

    saveCart()
    updateCart()
  }

  // Eliminar producto del carrito
  function removeFromCart(id) {
    const itemIndex = cart.findIndex((item) => item.id === id)
    if (itemIndex === -1) return

    const removedItem = cart[itemIndex]
    cart.splice(itemIndex, 1)

    saveCart()
    updateCart()
    showNotification(`${removedItem.name} eliminado del carrito`)
  }

  // Mostrar notificación: crea y muestra una notificación temporal
  function showNotification(message) {
    const notification = document.createElement("div")
    notification.className = "notification"
    notification.innerHTML = `<p>${message}</p>`
    document.body.appendChild(notification)

    setTimeout(() => {
      notification.classList.add("fade-out")
      setTimeout(() => {
        document.body.removeChild(notification)
      }, 500)
    }, 3000)
  }

  // Finalizar compra: muestra el modal de checkout
  function checkout() {
    if (cart.length === 0 || !checkoutModal) return

    checkoutModal.style.display = "block"
  }

  // Enviar pedido: procesa el formulario de checkout
  function submitOrder(e) {
    e.preventDefault()

    const customerName = document.getElementById("customer-name").value
    const customerEmail = document.getElementById("customer-email").value
    const paymentMethod = document.querySelector('input[name="payment"]:checked').value

    // Crear mensaje para WhatsApp
    let message = `*Nuevo Pedido de ${customerName}*\n\n`
    message += `*Productos:*\n`

    cart.forEach((item) => {
      message += `- ${item.name} x${item.quantity} - ${formatPrice(item.price * item.quantity)}\n`
    })

    const total = cart.reduce((total, item) => total + item.price * item.quantity, 0)

    message += `\n*Total:* ${formatPrice(total)}`
    message += `\n*Método de pago:* ${getPaymentMethodName(paymentMethod)}`
    message += `\n*Email:* ${customerEmail}`

    // Codificar mensaje para URL
    const encodedMessage = encodeURIComponent(message)

    // Abrir WhatsApp con el mensaje
    window.open(`https://wa.me/5491122834351?text=${encodedMessage}`, "_blank")

    // Cerrar modal y limpiar carrito
    if (checkoutModal) checkoutModal.style.display = "none"
    cart = []
    saveCart()
    updateCart()
    showNotification("¡Pedido enviado con éxito!")
  }

  // Obtener nombre del método de pago
  function getPaymentMethodName(method) {
    switch (method) {
      case "efectivo":
        return "Efectivo"
      case "mercadopago":
        return "Mercado Pago"
      case "transferencia":
        return "Transferencia Bancaria"
      default:
        return method
    }
  }

  // Función para controlar el slider de novedades
  function showSlide(index) {
    if (!slides || slides.length === 0) return;
    
    // Ocultar todas las diapositivas
    slides.forEach((slide) => {
      slide.classList.remove("active")
    })

    // Desactivar todos los indicadores
    if (indicatorDots) {
      indicatorDots.forEach((dot) => {
        dot.classList.remove("active")
      })
    }

    // Mostrar la diapositiva actual
    slides[index].classList.add("active")

    // Activar el indicador correspondiente
    if (indicatorDots && indicatorDots[index]) {
      indicatorDots[index].classList.add("active")
    }

    // Actualizar el índice actual
    currentSlide = index
  }

  // Función para avanzar al siguiente slide
  function nextSlide() {
    if (!slides || slides.length === 0) return;
    
    let next = currentSlide + 1
    if (next >= slides.length) {
      next = 0
    }
    showSlide(next)
  }

  // Iniciar el slider automático
  function startSlider() {
    if (slides && slides.length > 0) {
      // Mostrar el primer slide
      showSlide(0)

      // Configurar el intervalo para cambiar automáticamente
      setInterval(nextSlide, 3000)
    }
  }

  // Crear botón flotante para cerrar en móviles
  function createMobileCloseButton() {
    // Verificar si ya existe el botón
    if (document.querySelector(".mobile-close-btn")) return

    const mobileCloseBtn = document.createElement("button")
    mobileCloseBtn.className = "mobile-close-btn"
    mobileCloseBtn.innerHTML = '<i class="fas fa-times"></i>'
    mobileCloseBtn.style.display = "none"

    document.body.appendChild(mobileCloseBtn)

    // Evento para cerrar el modal
    mobileCloseBtn.addEventListener("click", function () {
      if (productModal) productModal.style.display = "none"
      this.style.display = "none"
    })

    return mobileCloseBtn
  }

  // Crear el botón móvil
  const mobileCloseBtn = createMobileCloseButton()

  // Función para cerrar el modal
  function closeModal() {
    if (productModal) productModal.style.display = "none"
    if (mobileCloseBtn) mobileCloseBtn.style.display = "none"
  }

// Cargar productos desde la API
async function cargarProductos() {
  try {
    // Añadir un parámetro de tiempo para evitar el caché
    const timestamp = new Date().getTime();
    const response = await fetch(`http://localhost/bear_shop/api/productos.php?t=${timestamp}`);
    if (!response.ok) {
      throw new Error('Error al cargar los productos');
    }
    
    products = await response.json();
    filteredProducts = [...products];
    
    // Renderizar productos una vez cargados
    renderProducts();
    
  } catch (error) {
    console.error('Error:', error);
    showNotification('No se pudieron cargar los productos. Intenta de nuevo más tarde.');
  }
}

  // Agregar función para guardar el carrito en localStorage
  function saveCart() {
    localStorage.setItem("bearShopCart", JSON.stringify(cart))
  }

  // Cargar carrito desde localStorage al inicio
  function loadCart() {
    const storedCart = localStorage.getItem("bearShopCart")
    if (storedCart) {
      try {
        cart = JSON.parse(storedCart)
        updateCart()
      } catch (e) {
        console.error("Error parsing saved cart:", e)
      }
    }
  }

  // Eventos

  // Mobile menu toggle: controla la visualización del menú en dispositivos móviles
  if (menuToggle && menu) {
    menuToggle.addEventListener("click", () => {
      menu.classList.toggle("active")
    })
  }

  // Cerrar menú al hacer clic fuera
  document.addEventListener("click", (event) => {
    if (menu && !event.target.closest(".menu") && !event.target.closest("#menuToggle") && menu.classList.contains("active")) {
      menu.classList.remove("active")
    }
  })

  // Cargar más productos
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", (e) => {
      e.preventDefault()
      loadMoreProducts()
    })
  }

  // Buscar productos
  if (searchBtn) {
    searchBtn.addEventListener("click", () => {
      searchProducts()
    })
  }

  if (searchInput) {
    searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        searchProducts()
      }
    })
  }

  // Limpiar búsqueda
  if (clearSearchBtn) {
    clearSearchBtn.addEventListener("click", clearSearch)
  }

  // Finalizar compra
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", checkout)
  }

  // Cerrar modal de checkout
  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", () => {
      if (checkoutModal) checkoutModal.style.display = "none"
    })
  }

  // Cerrar modal de producto
  if (productModalClose) {
    productModalClose.addEventListener("click", closeModal)
  }

  // Cerrar modales al hacer clic fuera
  window.addEventListener("click", (event) => {
    if (checkoutModal && event.target === checkoutModal) {
      checkoutModal.style.display = "none"
    }
    if (productModal && event.target === productModal) {
      closeModal()
    }
  })

  // Enviar pedido
  if (checkoutForm) {
    checkoutForm.addEventListener("submit", submitOrder)
  }

  // Control de cantidad en el modal de producto
  if (modalQuantityDecrease) {
    modalQuantityDecrease.addEventListener("click", () => {
      if (!modalQuantity) return;
      const quantity = Number.parseInt(modalQuantity.value)
      if (quantity > 1) {
        modalQuantity.value = quantity - 1
      }
    })
  }

  if (modalQuantityIncrease) {
    modalQuantityIncrease.addEventListener("click", () => {
      if (!modalQuantity) return;
      const quantity = Number.parseInt(modalQuantity.value)
      modalQuantity.value = quantity + 1
    })
  }

  if (modalQuantity) {
    modalQuantity.addEventListener("change", function () {
      const quantity = Number.parseInt(this.value)
      if (quantity < 1) {
        this.value = 1
      }
    })
  }

  // Agregar al carrito desde el modal
  if (modalAddToCart) {
    modalAddToCart.addEventListener("click", () => {
      if (currentProductId && modalQuantity) {
        const quantity = Number.parseInt(modalQuantity.value)
        addToCart(currentProductId, quantity)
        if (productModal) productModal.style.display = "none"
        if (mobileCloseBtn) mobileCloseBtn.style.display = "none"
      }
    })
  }

  // Agregar evento para las tarjetas de categoría
  document.querySelectorAll(".category-card").forEach((card) => {
    card.addEventListener("click", function () {
      const category = this.getAttribute("data-category")
      if (searchInput) searchInput.value = category
      searchProducts()

      // Scroll a la sección de productos
      const productosSection = document.querySelector("#productos")
      if (productosSection) productosSection.scrollIntoView({ behavior: "smooth" })
    })
  })

  // Eventos para el slider de novedades
  if (indicators) {
    // Agregar eventos a los indicadores para cambiar manualmente
    indicatorDots.forEach((dot, index) => {
      dot.addEventListener("click", () => {
        showSlide(index)
      })
    })
  }

  // Agregar delegación de eventos para los botones de cantidad en el carrito
  if (cartItemsContainer) {
    cartItemsContainer.addEventListener("click", (e) => {
      // Verificar si se hizo clic en un botón de incremento o decremento
      if (e.target.classList.contains("quantity-btn") || e.target.closest(".quantity-btn")) {
        e.preventDefault()
        e.stopPropagation()

        const button = e.target.classList.contains("quantity-btn") ? e.target : e.target.closest(".quantity-btn")
        const productId = Number.parseInt(button.getAttribute("data-id"))

        if (button.classList.contains("increase")) {
          updateItemQuantity(productId, "increase")
        } else if (button.classList.contains("decrease")) {
          updateItemQuantity(productId, "decrease")
        }
      }

      // Verificar si se hizo clic en el botón de eliminar
      if (e.target.classList.contains("remove-item") || e.target.closest(".remove-item")) {
        e.preventDefault()
        e.stopPropagation()

        const button = e.target.classList.contains("remove-item") ? e.target : e.target.closest(".remove-item")
        const productId = Number.parseInt(button.getAttribute("data-id"))
        removeFromCart(productId)
      }
    })
  }

  // Ajustar cuando cambia el tamaño de la ventana
  window.addEventListener("resize", () => {
    if (productModal && productModal.style.display === "block") {
      if (window.innerWidth <= 768 && mobileCloseBtn) {
        mobileCloseBtn.style.display = "flex"
      } else if (mobileCloseBtn) {
        mobileCloseBtn.style.display = "none"
      }
    }
  })
  

  // Inicializar
  cargarProductos() // Cargar productos desde la API
  loadCart() // Cargar carrito desde localStorage
  startSlider() // Iniciar el slider de novedades
})