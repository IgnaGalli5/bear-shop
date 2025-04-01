// Declarar las variables productGrid, filteredProducts, visibleProducts y loadMoreBtn
const productGrid = document.getElementById("product-grid") // Asume que tienes un elemento con el id "product-grid"
// Declaración de variables globales
let products = [] // Cambiado de const a let para permitir reasignación
let filteredProducts = [] // Inicializar con un array vacío
let visibleProducts = 8 // Valor inicial, puedes ajustarlo
const loadMoreBtn = document.getElementById("load-more") // Asume que tienes un botón con el id "load-more"

// Declarar las variables addToCart y showNotification
// Función para agregar al carrito
function addToCart(productId, quantity = 1) {
  console.log("Agregando al carrito:", productId, "cantidad:", quantity)

  // Buscar el producto
  const product = products.find((p) => p.id === productId)
  if (!product) {
    console.error("Producto no encontrado:", productId)
    return
  }

  // Obtener el carrito actual
  const cart = getCart()

  // Verificar si el producto ya está en el carrito
  const existingItem = cart.find((item) => item.id === productId)

  if (existingItem) {
    existingItem.quantity += quantity
  } else {
    cart.push({
      id: product.id,
      name: product.name,
      price: product.price,
      image: product.image,
      quantity: quantity,
    })
  }

  // Guardar el carrito actualizado
  saveCart(cart)

  // Actualizar la interfaz
  updateCartUI()

  // Mostrar notificación
  showNotification(`${product.name} agregado al carrito`)
}

// Función para mostrar notificaciones
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

// Actualizar el total del carrito
function updateCartTotal() {
  const cartItems = document.querySelectorAll(".cart-item")
  let subtotal = 0

  cartItems.forEach((item) => {
    const priceElement = item.querySelector(".item-price")
    const quantityElement = item.querySelector(".item-quantity")

    if (!priceElement || !quantityElement) {
      console.warn("Elementos de precio o cantidad no encontrados en el item del carrito")
      return
    }

    const price = Number.parseFloat(priceElement.getAttribute("data-price") || 0)
    const quantity = Number.parseInt(quantityElement.textContent || 1)
    subtotal += price * quantity
  })

  // Actualizar subtotal
  const cartSubtotal = document.getElementById("cart-subtotal")
  if (cartSubtotal) {
    cartSubtotal.textContent = "$" + formatPrice(subtotal)
  }

  // Verificar método de pago seleccionado
  const paymentEfectivo = document.getElementById("payment-efectivo")
  let total = subtotal

  // Aplicar descuento si el pago es en efectivo
  if (paymentEfectivo && paymentEfectivo.checked) {
    // Obtener el porcentaje de descuento en efectivo (si está disponible)
    const descuentoEfectivo = window.cashDiscountPercent || 10 // Usar 10% como valor predeterminado
    total = subtotal * (1 - descuentoEfectivo / 100)
  }

  // Actualizar total
  const cartTotal = document.getElementById("cart-total")
  if (cartTotal) {
    cartTotal.textContent = "$" + formatPrice(total)
  }

  // Mostrar u ocultar elementos del carrito según corresponda
  const emptyCart = document.getElementById("empty-cart")
  const cartSummary = document.getElementById("cart-summary")

  if (cartItems.length > 0) {
    if (emptyCart) emptyCart.style.display = "none"
    if (cartSummary) cartSummary.style.display = "block"
  } else {
    if (emptyCart) emptyCart.style.display = "block"
    if (cartSummary) cartSummary.style.display = "none"
  }
}

// Formatear precio para mostrar
function formatPrice(price) {
  // Asegurarse de que price sea un número
  if (typeof price !== "number" || isNaN(price)) {
    console.error("formatPrice recibió un valor no numérico:", price)
    price = 0
  }

  return price.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, ".")
}

// Agregar event listeners a los métodos de pago
document.addEventListener("DOMContentLoaded", () => {
  const paymentMethods = document.querySelectorAll('input[name="payment"]')
  paymentMethods.forEach((method) => {
    method.addEventListener("change", updateCartTotals)
  })
})

// Añadir estilo para la etiqueta de descuento si no existe
if (!document.querySelector("style#discount-label-style")) {
  const discountStyle = document.createElement("style")
  discountStyle.id = "discount-label-style"
  discountStyle.textContent = `
    .discount-label {
      color: #e74c3c;
      font-weight: bold;
      font-size: 0.85em;
      display: block;
      margin-top: 5px;
    }
  `
  document.head.appendChild(discountStyle)
}

// Añadir estilo para la etiqueta de envío gratis si no existe
if (!document.querySelector("style#free-shipping-label-style")) {
  const freeShippingStyle = document.createElement("style")
  freeShippingStyle.id = "free-shipping-label-style"
  freeShippingStyle.textContent = `
    .free-shipping-label {
      color: #2ecc71;
      font-weight: bold;
      font-size: 0.85em;
      display: block;
      margin-top: 5px;
    }
  `
  document.head.appendChild(freeShippingStyle)
}

// Declarar openProductModal (asumiendo que está definida en otro archivo o contexto)
// Si 'openProductModal' es una función que necesitas importar, hazlo aquí.
// Por ejemplo: import { openProductModal } from './modal.js';
// Si es una función global, asegúrate de que esté definida antes de este script.

// Actualizar la función renderProducts para usar el descuento en efectivo dinámico
// Renderizar productos en la grilla
function renderProducts() {
  if (!productGrid) {
    console.error("No se encontró el elemento product-grid")
    return
  }

  console.log("Renderizando productos:", filteredProducts.length)
  productGrid.innerHTML = ""

  // Verificar que filteredProducts sea un array
  if (!Array.isArray(filteredProducts)) {
    console.error("filteredProducts no es un array:", filteredProducts)
    return
  }

  const productsToShow = filteredProducts.slice(0, visibleProducts)
  console.log("Productos a mostrar:", productsToShow.length)

  productsToShow.forEach((product) => {
    if (!product) {
      console.error("Producto indefinido encontrado")
      return
    }

    const productCard = document.createElement("div")
    productCard.className = "product-card"
    if (product.onSale) {
      productCard.classList.add("on-sale")
    }
    productCard.setAttribute("data-id", product.id)
    productCard.setAttribute("data-category", product.category)

    // Crear HTML para el precio (normal o promocional)
    let priceHTML = ""

    if (product.onSale && product.originalPrice) {
      // Producto con promoción normal
      const promoLabel = product.nombre_promocion ? product.nombre_promocion : "¡OFERTA!"

      // Calcular el porcentaje de descuento para mostrarlo
      let discountText = promoLabel
      if (product.tipo_promocion === "porcentaje" && product.valor_promocion) {
        discountText = `${promoLabel} - ${product.valor_promocion}% OFF`
      }

      priceHTML = `
        <p class="price">
          <span class="original-price">$${formatPrice(product.originalPrice)}</span> 
          $${formatPrice(product.price)}
        </p>
        <p class="discount-label">${discountText}</p>
      `
    } else {
      // Producto sin promoción - mostrar solo precio normal
      priceHTML = `
        <p class="price">$${formatPrice(product.price)}</p>
      `
    }

    productCard.innerHTML = `
      <div class="product-image" data-id="${product.id}">
        <img src="${product.image}" alt="${product.name}">
        ${product.onSale ? '<span class="discount-badge">Oferta</span>' : ""}
      </div>
      <div class="product-info">
        <h3>${product.name}</h3>
        <div class="product-price">
          ${priceHTML}
        </div>
        <div class="product-actions">
          <button class="btn-small view-product" data-id="${product.id}">Ver detalles</button>
          <button class="btn-small add-to-cart" data-id="${product.id}">Agregar</button>
        </div>
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

  // Agregar eventos a los botones
  setupProductButtons()

  // Agregar evento de clic a las imágenes de productos
  document.querySelectorAll(".product-image").forEach((image) => {
    image.addEventListener("click", function () {
      const productId = Number.parseInt(this.getAttribute("data-id"), 10)
      openProductModal(productId)
    })
  })
}

// Modificar la función cargarProductos para manejar el descuento en efectivo
// Función para cargar productos desde la API
async function cargarProductos() {
  try {
    console.log("Iniciando carga de productos...")

    // Añadir un parámetro de tiempo para evitar el caché
    const timestamp = new Date().getTime()
    const response = await fetch(`api/productos.php?t=${timestamp}`)

    // Verificar si la respuesta es exitosa
    if (!response.ok) {
      throw new Error(`Error HTTP: ${response.status} ${response.statusText}`)
    }

    // Obtener el texto de la respuesta para depuración
    const responseText = await response.text()
    console.log("Respuesta recibida:", responseText)

    // Intentar parsear la respuesta como JSON
    let data
    try {
      data = JSON.parse(responseText)
      console.log("Datos parseados:", data)
    } catch (parseError) {
      console.error("Error al parsear JSON:", parseError)
      throw new Error("La respuesta del servidor no es un JSON válido")
    }

    // Verificar si la respuesta contiene un error
    if (data && data.error === true) {
      console.error("Error del servidor:", data.message)
      throw new Error(data.message)
    }

    // Verificar que data sea un array
    if (!Array.isArray(data)) {
      console.error("La respuesta no es un array:", data)
      data = [] // Asegurar que sea un array vacío si no lo es
    }

    // Asignar a la variable global products
    products = data
    console.log("Products asignado:", products)

    // Guardar el porcentaje de descuento en efectivo globalmente
    if (products.length > 0 && products[0].cashDiscountPercent) {
      window.cashDiscountPercent = products[0].cashDiscountPercent

      // Actualizar el texto del radio button de efectivo
      const efectivoLabel = document.querySelector('label[for="payment-efectivo"] span')
      if (efectivoLabel) {
        efectivoLabel.textContent = `Efectivo (${window.cashDiscountPercent}% de descuento)`
      }
    }

    // Asignar a filteredProducts
    filteredProducts = [...products]
    console.log("FilteredProducts asignado:", filteredProducts)

    // Renderizar productos una vez cargados
    renderProducts()

    return products
  } catch (error) {
    console.error("Error al cargar productos:", error)
    showNotification("No se pudieron cargar los productos. Intenta de nuevo más tarde.")

    // Inicializar como array vacío en caso de error
    products = []
    filteredProducts = []

    // Intentar renderizar aunque sea con un array vacío
    renderProducts()

    return []
  }
}

// Mostrar productos en la página
function mostrarProductos(productos) {
  const productGrid = document.getElementById("product-grid")
  if (!productGrid) return

  // Limpiar el contenedor
  productGrid.innerHTML = ""

  productos.forEach((producto) => {
    const productCard = document.createElement("div")
    productCard.className = "product-card"
    productCard.setAttribute("data-id", producto.id)
    productCard.setAttribute("data-category", producto.category)

    // Crear HTML para el precio (normal o promocional)
    let priceHTML = ""

    if (producto.onSale && producto.originalPrice) {
      // Producto con promoción normal
      const promoLabel = producto.nombre_promocion ? producto.nombre_promocion : "¡OFERTA!"
      priceHTML = `
        <p class="price">
          <span class="original-price">$${formatPrice(producto.originalPrice)}</span> 
          $${formatPrice(producto.price)}
        </p>
        <p class="discount-label">${promoLabel}</p>
      `
    } else {
      // Producto sin promoción - mostrar solo precio normal
      priceHTML = `
        <p class="price">$${formatPrice(producto.price)}</p>
      `
    }

    productCard.innerHTML = `
      <div class="product-image">
        <img src="${producto.image}" alt="${producto.name}">
        ${producto.onSale ? '<span class="discount-badge">Oferta</span>' : ""}
      </div>
      <div class="product-info">
        <h3>${producto.name}</h3>
        <div class="product-price">
          ${priceHTML}
        </div>
        <div class="product-actions">
          <button class="btn-small view-product" data-id="${producto.id}">Ver detalles</button>
          <button class="btn-small add-to-cart" data-id="${producto.id}">Agregar</button>
        </div>
      </div>
    `

    productGrid.appendChild(productCard)
  })

  // Agregar eventos a los botones
  document.querySelectorAll(".view-product").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const productId = this.getAttribute("data-id")
      abrirModalProducto(productId)
    })
  })

  document.querySelectorAll(".add-to-cart").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const productId = this.getAttribute("data-id")
      agregarAlCarrito(productId)
    })
  })
}

// Inicializar cuando se carga la página
document.addEventListener("DOMContentLoaded", () => {
  console.log("Documento cargado, inicializando...")

  // Inicializar carrito
  updateCartUI()

  // Cargar productos
  cargarProductos()
    .then(() => {
      console.log("Productos cargados exitosamente")
    })
    .catch((error) => {
      console.error("Error al cargar productos:", error)
    })

  // Configurar eventos
  setupEvents()
})

// Asegurar que el HTML tenga los IDs correctos
document.addEventListener("DOMContentLoaded", () => {
  // Verificar que los elementos existan y tengan los IDs correctos
  const cartSubtotal = document.getElementById("cart-subtotal")
  const cartShipping = document.getElementById("cart-shipping")
  const cartTotal = document.getElementById("cart-total")

  if (!cartSubtotal) {
    console.error("No se encontró el elemento cart-subtotal")
  }

  if (!cartShipping) {
    console.error("No se encontró el elemento cart-shipping")
  }

  if (!cartTotal) {
    console.error("No se encontró el elemento cart-total")
  }

  // Inicializar los valores
  if (cartSubtotal) cartSubtotal.textContent = "$0"
  if (cartShipping) cartShipping.textContent = "$0"
  if (cartTotal) cartTotal.textContent = "$0"

  // Actualizar carrito si hay elementos
  updateCartUI()
})

// Variables globales
const cart = [] // Carrito de compras
const currentProductId = null // ID del producto actualmente mostrado en el modal
const currentSlide = 0 // Índice de la diapositiva actual en el slider de novedades
// Variable global para el porcentaje de descuento en efectivo
window.cashDiscountPercent = 10 // Valor por defecto, se actualizará desde la API

// Elementos DOM - Referencias a elementos HTML para manipularlos con JavaScript
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

// Declarar abrirModalProducto y agregarAlCarrito
let abrirModalProducto
let agregarAlCarrito

abrirModalProducto = (productId) => {
  // Implementación de la función abrirModalProducto
  console.log("Opening modal for product ID:", productId)
  // Aquí iría la lógica para abrir el modal con la información del producto
}

agregarAlCarrito = (productId) => {
  // Implementación de la función agregarAlCarrito
  console.log("Adding product to cart with ID:", productId)
  // Aquí iría la lógica para agregar el producto al carrito
}

// Función para depurar cálculos del carrito
function debugCartCalculations() {
  const cart = getCart()
  console.log("--- DEBUG: Cálculos del carrito ---")

  // Subtotal
  const subtotal = cart.reduce((total, item) => {
    const itemTotal = item.price * item.quantity
    console.log(`Producto: ${item.name}, Precio: ${item.price}, Cantidad: ${item.quantity}, Total: ${itemTotal}`)
    return total + itemTotal
  }, 0)
  console.log(`Subtotal calculado: ${subtotal}`)

  // Envío
  const FREE_SHIPPING_THRESHOLD = 60000
  const SHIPPING_COST = 2500
  const shippingCost = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST
  console.log(`Costo de envío: ${shippingCost} (Umbral para envío gratis: ${FREE_SHIPPING_THRESHOLD})`)

  // Total
  const totalWithShipping = subtotal + shippingCost
  console.log(`Total con envío: ${totalWithShipping}`)

  // Descuento por efectivo
  const descuentoEfectivo = window.cashDiscountPercent || 10
  const totalConDescuento = totalWithShipping * (1 - descuentoEfectivo / 100)
  console.log(`Total con descuento por efectivo (${descuentoEfectivo}%): ${totalConDescuento}`)

  console.log("--- FIN DEBUG ---")
}

// Agregar esta función para asegurarnos de que los elementos del carrito tengan la estructura correcta
function validateCartItems() {
  const cart = getCart()
  let modified = false

  const validatedCart = cart.map((item) => {
    // Asegurarse de que price sea un número
    if (typeof item.price !== "number" || isNaN(item.price)) {
      console.warn(`Item con precio inválido: ${item.name}, price: ${item.price}`)
      item.price = Number.parseFloat(item.price) || 0
      modified = true
    }

    // Asegurarse de que quantity sea un número entero
    if (typeof item.quantity !== "number" || isNaN(item.quantity) || item.quantity < 1) {
      console.warn(`Item con cantidad inválida: ${item.name}, quantity: ${item.quantity}`)
      item.quantity = Number.parseInt(item.quantity) || 1
      modified = true
    }

    return item
  })

  if (modified) {
    console.log("El carrito fue corregido debido a valores inválidos")
    saveCart(validatedCart)
  }

  return validatedCart
}

// Modificar getCart para validar los elementos
// Obtener el carrito desde localStorage
function getCart() {
  try {
    const cartData = localStorage.getItem("bearShopCart")
    if (!cartData) return []

    const parsedCart = JSON.parse(cartData)
    if (!Array.isArray(parsedCart)) {
      console.error("El carrito no es un array:", parsedCart)
      return []
    }

    // Validar cada elemento del carrito
    return parsedCart.map((item) => {
      // Asegurar que cada elemento tenga las propiedades necesarias y los tipos correctos
      return {
        id: Number(item.id) || 0,
        name: String(item.name || "Producto"),
        price: Number(item.price) || 0,
        image: String(item.image || "productos/default.jpg"),
        quantity: Number(item.quantity) || 1,
      }
    })
  } catch (error) {
    console.error("Error al obtener el carrito:", error)
    return []
  }
}

// Guardar el carrito en localStorage
function saveCart(cart) {
  try {
    // Asegurar que sea un array
    if (!Array.isArray(cart)) {
      console.error("Intentando guardar un carrito que no es array:", cart)
      cart = []
    }

    localStorage.setItem("bearShopCart", JSON.stringify(cart))
  } catch (error) {
    console.error("Error al guardar el carrito:", error)
  }
}

// Formatear precio: convierte un número a formato de moneda
// Renderizar productos: muestra los productos en la grilla areglado recien
// Abrir modal de producto: muestra los detalles de un producto
// Configurar botones de productos
function setupProductButtons() {
  // Botones "Ver detalles"
  document.querySelectorAll(".view-product").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const productId = Number.parseInt(this.getAttribute("data-id"), 10)
      openProductModal(productId)
    })
  })

  // Botones "Agregar al carrito"
  document.querySelectorAll(".add-to-cart").forEach((button) => {
    button.addEventListener("click", function (e) {
      e.preventDefault()
      const productId = Number.parseInt(this.getAttribute("data-id"), 10)
      addToCart(productId)
    })
  })
}

// Función para abrir el modal de producto
function openProductModal(productId) {
  console.log("Abriendo modal para producto ID:", productId)

  const productModal = document.getElementById("product-modal")
  if (!productModal) {
    console.error("No se encontró el modal de producto")
    return
  }

  // Buscar el producto por ID
  const product = products.find((p) => p.id === productId)
  if (!product) {
    console.error("Producto no encontrado:", productId)
    return
  }

  // Actualizar contenido del modal
  const modalProductName = document.getElementById("modal-product-name")
  const modalProductPrice = document.querySelector(".modal-product-price")
  const modalProductDescription = document.getElementById("modal-product-description")
  const modalProductFeatures = document.getElementById("modal-product-features")
  const modalProductUsage = document.getElementById("modal-product-usage")
  const modalProductImage = document.querySelector(".product-detail-image")
  const modalRatingCount = document.querySelector(".rating-count")

  if (modalProductName) modalProductName.textContent = product.name

  // Actualizar precio en el modal
  if (modalProductPrice) {
    if (product.onSale && product.originalPrice) {
      // Producto con promoción - mostrar precio original y precio con descuento
      const promoLabel = product.nombre_promocion ? product.nombre_promocion : "¡OFERTA!"

      // Calcular el porcentaje de descuento para mostrarlo
      let discountText = promoLabel
      if (product.tipo_promocion === "porcentaje" && product.valor_promocion) {
        discountText = `${promoLabel} - ${product.valor_promocion}% OFF`
      }

      modalProductPrice.innerHTML = `
        <span class="original-price">$${formatPrice(product.originalPrice)}</span> 
        $${formatPrice(product.price)}
        <div class="discount-label">${discountText}</div>
      `
    } else {
      // Producto sin promoción - mostrar solo precio normal
      modalProductPrice.innerHTML = `$${formatPrice(product.price)}`
    }
  }

  if (modalProductDescription) modalProductDescription.textContent = product.description || "Sin descripción disponible"
  if (modalProductUsage) modalProductUsage.textContent = product.usage || "Sin instrucciones de uso disponibles"
  if (modalRatingCount) modalRatingCount.textContent = `(${product.ratingCount || 0} reseñas)`

  // Actualizar estrellas según la calificación
  const stars = productModal.querySelectorAll(".stars i")
  const rating = product.rating || 0
  const fullStars = Math.floor(rating)
  const hasHalfStar = rating % 1 >= 0.5

  stars.forEach((star, index) => {
    if (index < fullStars) {
      star.className = "fas fa-star" // Estrella completa
    } else if (index === fullStars && hasHalfStar) {
      star.className = "fas fa-star-half-alt" // Media estrella
    } else {
      star.className = "far fa-star" // Estrella vacía
    }
  })

  // Actualizar características
  if (modalProductFeatures) {
    modalProductFeatures.innerHTML = ""
    if (product.features && Array.isArray(product.features)) {
      product.features.forEach((feature) => {
        const li = document.createElement("li")
        li.textContent = feature
        modalProductFeatures.appendChild(li)
      })
    } else {
      const li = document.createElement("li")
      li.textContent = "No hay características disponibles"
      modalProductFeatures.appendChild(li)
    }
  }

  // Configurar carrusel de imágenes
  if (product.images && Array.isArray(product.images) && product.images.length > 0) {
    setupProductImageCarousel(product.images, modalProductImage)
  } else if (product.image) {
    modalProductImage.innerHTML = `<img src="${product.image}" alt="${product.name}">`
  } else {
    modalProductImage.innerHTML = `<img src="productos/default.jpg" alt="${product.name}">`
  }

  // Resetear cantidad
  const modalQuantity = document.getElementById("modal-quantity")
  if (modalQuantity) modalQuantity.value = 1

  // Mostrar modal
  productModal.style.display = "block"
}

// Cargar más productos: aumenta la cantidad de productos visibles
function loadMoreProducts() {
  visibleProducts += 4
  renderProducts()
}

// Buscar productos: filtra los productos según el término de búsqueda
function searchProducts() {
  const searchInput = document.getElementById("product-search")
  const filterResults = document.getElementById("filter-results")
  const searchTerm = document.getElementById("search-term")

  if (!searchInput) return

  const query = searchInput.value.trim().toLowerCase()

  if (query === "") {
    filteredProducts = [...products]
    if (filterResults) filterResults.style.display = "none"
  } else {
    filteredProducts = products.filter(
      (product) =>
        product.name.toLowerCase().includes(query) ||
        (product.category && product.category.toLowerCase().includes(query)) ||
        (product.description && product.description.toLowerCase().includes(query)),
    )

    if (searchTerm) searchTerm.textContent = `"${query}"`
    if (filterResults) filterResults.style.display = "flex"
  }

  visibleProducts = 8
  renderProducts()
}

// Limpiar búsqueda: restablece los productos mostrados
// Limpiar búsqueda
function clearSearch() {
  const searchInput = document.getElementById("product-search")
  const filterResults = document.getElementById("filter-results")

  if (searchInput) searchInput.value = ""
  filteredProducts = [...products]
  if (filterResults) filterResults.style.display = "none"

  visibleProducts = 8
  renderProducts()
}

// Agregar producto al carrito

// Actualizar carrito: actualiza la visualización del carrito y los totales
// Obtener el carrito desde localStorage
// Guardar el carrito en localStorage

// Actualizar la interfaz del carrito
function updateCartUI() {
  const cart = getCart()
  const cartCount = document.querySelector(".cart-count")
  const cartItemsContainer = document.getElementById("cart-items")
  const emptyCartMessage = document.getElementById("empty-cart")
  const cartSummary = document.getElementById("cart-summary")

  // Actualizar contador
  if (cartCount) {
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0)
    cartCount.textContent = totalItems.toString()
  }

  // Si no hay elementos en el carrito
  if (cart.length === 0) {
    if (cartItemsContainer) cartItemsContainer.innerHTML = "" // Asegurarse de limpiar el contenedor
    if (emptyCartMessage) emptyCartMessage.style.display = "block"
    if (cartSummary) cartSummary.style.display = "none"
    return
  }

  // Ocultar mensaje de carrito vacío y mostrar resumen
  if (emptyCartMessage) emptyCartMessage.style.display = "none"
  if (cartSummary) cartSummary.style.display = "block"

  // Renderizar items del carrito
  if (cartItemsContainer) {
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
          <p class="item-price" data-price="${item.price}">$${formatPrice(item.price)}</p>
          <p class="item-quantity">Cantidad: <span>${item.quantity}</span></p>
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

    // Agregar eventos a los botones
    setupCartButtons()
  }

  // Actualizar totales
  updateCartTotals()
}

// Actualizar cantidad de un item en el carrito
// Configurar botones del carrito
function setupCartButtons() {
  // Botones de disminuir cantidad
  document.querySelectorAll(".quantity-btn.decrease").forEach((button) => {
    button.addEventListener("click", function () {
      const id = Number.parseInt(this.getAttribute("data-id"), 10)
      updateCartItemQuantity(id, "decrease")
    })
  })

  // Botones de aumentar cantidad
  document.querySelectorAll(".quantity-btn.increase").forEach((button) => {
    button.addEventListener("click", function () {
      const id = Number.parseInt(this.getAttribute("data-id"), 10)
      updateCartItemQuantity(id, "increase")
    })
  })

  // Inputs de cantidad
  document.querySelectorAll(".quantity-input").forEach((input) => {
    input.addEventListener("change", function () {
      const id = Number.parseInt(this.getAttribute("data-id"), 10)
      const value = Number.parseInt(this.value, 10)

      if (value < 1) {
        this.value = 1
        updateCartItemQuantity(id, "set", 1)
      } else {
        updateCartItemQuantity(id, "set", value)
      }
    })
  })

  // Botones de eliminar
  document.querySelectorAll(".remove-item").forEach((button) => {
    button.addEventListener("click", function () {
      const id = Number.parseInt(this.getAttribute("data-id"), 10)
      removeFromCart(id)
    })
  })
}

// Actualizar cantidad de un item en el carrito
function updateCartItemQuantity(id, action, value = null) {
  const cart = getCart()
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

  saveCart(cart)
  updateCartUI()
}

// Eliminar producto del carrito
// Eliminar un producto del carrito
function removeFromCart(id) {
  const cart = getCart()
  const itemIndex = cart.findIndex((item) => item.id === id)

  if (itemIndex === -1) return

  const removedItem = cart[itemIndex]
  cart.splice(itemIndex, 1)

  saveCart(cart)
  updateCartUI()

  showNotification(`${removedItem.name} eliminado del carrito`)

  // Si el carrito está vacío después de eliminar el producto, actualizar la interfaz
  if (cart.length === 0) {
    const cartItemsContainer = document.getElementById("cart-items")
    if (cartItemsContainer) {
      cartItemsContainer.innerHTML = ""
    }

    const emptyCartMessage = document.getElementById("empty-cart")
    const cartSummary = document.getElementById("cart-summary")

    if (emptyCartMessage) emptyCartMessage.style.display = "block"
    if (cartSummary) cartSummary.style.display = "none"
  }
}

// Mostrar notificación: crea y muestra una notificación temporal
// Actualizar totales del carrito
function updateCartTotals() {
  const cart = getCart()

  // Obtener elementos del DOM
  const cartSubtotalElement = document.getElementById("cart-subtotal")
  const cartTotalElement = document.getElementById("cart-total")

  // Si el carrito está vacío, mostrar valores en cero y salir
  if (cart.length === 0) {
    if (cartSubtotalElement) cartSubtotalElement.textContent = "$0"
    if (cartTotalElement) cartTotalElement.textContent = "$0"
    return
  }

  // Calcular subtotal
  const subtotal = cart.reduce((total, item) => {
    const itemTotal = Number(item.price) * Number(item.quantity)
    return total + itemTotal
  }, 0)

  // Configuración de envío gratuito
  const FREE_SHIPPING_THRESHOLD = 60000 // $60.000 para envío gratis
  const SHIPPING_COST = 2500 // Costo de envío estándar

  // Determinar si aplica envío gratuito
  const shippingCost = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST

  // Mostrar subtotal y mensaje de envío gratis si corresponde
  if (cartSubtotalElement) {
    if (subtotal < FREE_SHIPPING_THRESHOLD) {
      const amountLeft = FREE_SHIPPING_THRESHOLD - subtotal
      cartSubtotalElement.innerHTML = `$${formatPrice(subtotal)} <small>(Faltan $${formatPrice(amountLeft)} para envío gratis)</small>`
    } else {
      cartSubtotalElement.innerHTML = `$${formatPrice(subtotal)} <small class="free-shipping-label">(¡Envío gratis!)</small>`
    }
  }

  // Calcular total (subtotal + envío)
  const totalWithShipping = subtotal + shippingCost

  // Verificar método de pago seleccionado
  const paymentEfectivo = document.getElementById("payment-efectivo")
  let finalTotal = totalWithShipping

  // Aplicar descuento si el pago es en efectivo
  if (paymentEfectivo && paymentEfectivo.checked) {
    const descuentoEfectivo = window.cashDiscountPercent || 10
    finalTotal = totalWithShipping * (1 - descuentoEfectivo / 100)

    if (cartTotalElement) {
      cartTotalElement.innerHTML = `$${formatPrice(finalTotal)} <small class="discount-label">(${descuentoEfectivo}% descuento aplicado)</small>`
    }
  } else {
    if (cartTotalElement) {
      cartTotalElement.textContent = `$${formatPrice(totalWithShipping)}`
    }
  }

  // Guardar el costo de envío en una variable global para usarlo en el checkout
  window.currentShippingCost = shippingCost
}

// Finalizar compra: muestra el modal de checkout
function checkout() {
  if (cart.length === 0 || !checkoutModal) return

  checkoutModal.style.display = "block"
}

// Enviar pedido: procesa el formulario de checkout
// Procesar checkout
function processCheckout(event) {
  event.preventDefault()

  const cart = getCart()
  if (cart.length === 0) {
    showNotification("El carrito está vacío")
    return
  }

  // Obtener datos del formulario
  const customerName = document.getElementById("customer-name").value.trim()
  const customerEmail = document.getElementById("customer-email").value.trim()
  const customerPhone = document.getElementById("customer-celphone").value.trim()
  const customerAddress = document.getElementById("customer-adress").value.trim()
  const customerPostalCode = document.getElementById("customer-cp").value.trim()

  // Validar campos obligatorios
  if (!customerName || !customerEmail || !customerPhone || !customerAddress || !customerPostalCode) {
    showNotification("Por favor complete todos los campos")
    return
  }

  // Obtener método de pago seleccionado
  const paymentMethod = document.querySelector('input[name="payment"]:checked')
  if (!paymentMethod) {
    showNotification("Por favor seleccione un método de pago")
    return
  }

  const paymentMethodValue = paymentMethod.value
  const paymentMethodName = getPaymentMethodName(paymentMethodValue)

  // Calcular totales
  const subtotal = cart.reduce((total, item) => total + item.price * item.quantity, 0)

  // Determinar costo de envío
  const FREE_SHIPPING_THRESHOLD = 60000
  const SHIPPING_COST = 2500
  const shippingCost = subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST

  // Calcular total con envío
  let total = subtotal + shippingCost

  // Aplicar descuento si el pago es en efectivo
  if (paymentMethodValue === "efectivo") {
    const descuentoEfectivo = window.cashDiscountPercent || 10
    total = total * (1 - descuentoEfectivo / 100)
  }

  // Crear mensaje para WhatsApp
  let message = `*Nuevo Pedido de ${customerName}*\n\n`
  message += `*Datos del Cliente:*\n`
  message += `- Nombre: ${customerName}\n`
  message += `- Email: ${customerEmail}\n`
  message += `- Teléfono: ${customerPhone}\n`
  message += `- Dirección: ${customerAddress}\n`
  message += `- Código Postal: ${customerPostalCode}\n\n`

  message += `*Productos:*\n`
  cart.forEach((item, index) => {
    message += `${index + 1}. ${item.name} - $${formatPrice(item.price)} x ${item.quantity} = $${formatPrice(item.price * item.quantity)}\n`
  })

  message += `\n*Subtotal:* $${formatPrice(subtotal)}\n`
  message += `*Envío:* ${shippingCost > 0 ? `$${formatPrice(shippingCost)}` : "Gratis"}\n`
  message += `*Total:* $${formatPrice(total)}\n`
  message += `*Método de Pago:* ${paymentMethodName}\n`

  // Codificar mensaje para URL
  const encodedMessage = encodeURIComponent(message)

  // Número de WhatsApp (reemplazar con el número correcto)
  const whatsappNumber = "5491112345678" // Reemplazar con el número real

  // Crear URL de WhatsApp
  const whatsappURL = `https://wa.me/${whatsappNumber}?text=${encodedMessage}`

  // Mostrar notificación
  showNotification("Procesando pedido...")

  // Abrir WhatsApp en una nueva ventana
  window.open(whatsappURL, "_blank")

  // Limpiar carrito
  saveCart([])
  updateCartUI()

  // Cerrar modal
  const checkoutModal = document.getElementById("checkout-modal")
  if (checkoutModal) checkoutModal.style.display = "none"

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
  const slides = document.querySelectorAll("#news-slider .slide")
  const indicators = document.querySelectorAll("#slider-indicators .indicator")

  if (!slides || slides.length === 0) return

  // Ocultar todas las diapositivas
  slides.forEach((slide) => {
    slide.classList.remove("active")
  })

  // Desactivar todos los indicadores
  indicators.forEach((dot) => {
    dot.classList.remove("active")
  })

  // Mostrar la diapositiva actual
  slides[index].classList.add("active")

  // Activar el indicador correspondiente
  if (indicators[index]) {
    indicators[index].classList.add("active")
  }
}

// Función para avanzar al siguiente slide
function nextSlide() {
  const slides = document.querySelectorAll("#news-slider .slide")
  if (!slides || slides.length === 0) return

  // Encontrar el slide activo actual
  let currentIndex = 0
  slides.forEach((slide, index) => {
    if (slide.classList.contains("active")) {
      currentIndex = index
    }
  })

  // Calcular el siguiente índice
  let nextIndex = currentIndex + 1
  if (nextIndex >= slides.length) {
    nextIndex = 0
  }

  showSlide(nextIndex)
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
// Filtrar por categoría
function filterByCategory(category) {
  console.log("Filtrando por categoría:", category)

  const filterResults = document.getElementById("filter-results")
  const searchTerm = document.getElementById("search-term")

  if (!Array.isArray(products) || products.length === 0) {
    console.warn("No hay productos para filtrar")
    return
  }

  if (category) {
    // Normalizar la categoría para comparación
    const normalizedCategory = category.toLowerCase().trim()
    console.log("Categoría normalizada:", normalizedCategory)

    // Filtrar productos por categoría
    filteredProducts = products.filter((product) => {
      if (!product.category) {
        return false
      }
      const productCategory = product.category.toLowerCase().trim()
      console.log(`Producto ID: ${product.id}, Categoría: ${productCategory}`)
      return productCategory === normalizedCategory
    })

    console.log(`Se encontraron ${filteredProducts.length} productos en la categoría ${normalizedCategory}`)

    // Mostrar resultados del filtro
    if (filterResults) filterResults.style.display = "flex"
    if (searchTerm) {
      // Capitalizar primera letra de la categoría
      const displayCategory = normalizedCategory.charAt(0).toUpperCase() + normalizedCategory.slice(1)
      searchTerm.textContent = `Categoría: ${displayCategory}`
    }
  } else {
    // Si no hay categoría, mostrar todos los productos
    filteredProducts = [...products]
    if (filterResults) filterResults.style.display = "none"
  }

  // Resetear la cantidad de productos visibles y renderizar
  visibleProducts = 8
  renderProducts()
}

// Configurar carrusel de imágenes
// Modificar la función setupProductImageCarousel para ajustar las imágenes
function setupProductImageCarousel(images, container) {
  if (!container) {
    console.error("Contenedor de imágenes no encontrado")
    return
  }

  // Asegurarse de que images sea un array
  if (!Array.isArray(images)) {
    console.warn("images no es un array:", images)
    // Convertir a array si es posible
    if (typeof images === "string") {
      images = [images]
    } else if (!images) {
      images = ["productos/default.jpg"]
    } else {
      console.error("No se puede procesar el tipo de images:", typeof images)
      return
    }
  }

  if (images.length === 0) {
    images = ["productos/default.jpg"]
  }

  // Limpiar el contenedor
  container.innerHTML = ""

  // Si solo hay una imagen, mostrarla sin controles
  if (images.length === 1) {
    container.innerHTML = `<img src="${images[0]}" alt="Imagen del producto" style="max-width: 100%; max-height: 100%; width: auto; height: auto; object-fit: contain;">`
    return
  }

  // Crear estructura del carrusel
  const carouselHTML = `
    <div class="product-image-carousel">
      <div class="carousel-main">
        <img src="${images[0]}" alt="Imagen del producto">
        <button class="carousel-prev"><i class="fas fa-chevron-left"></i></button>
        <button class="carousel-next"><i class="fas fa-chevron-right"></i></button>
      </div>
      <div class="carousel-thumbnails">
        ${images
          .map(
            (img, index) => `
          <div class="thumbnail ${index === 0 ? "active" : ""}" data-index="${index}">
            <img src="${img}" alt="Miniatura ${index + 1}">
          </div>
        `,
          )
          .join("")}
      </div>
    </div>
  `

  container.innerHTML = carouselHTML

  // Configurar eventos
  const mainImg = container.querySelector(".carousel-main img")
  const prevBtn = container.querySelector(".carousel-prev")
  const nextBtn = container.querySelector(".carousel-next")
  const thumbnails = container.querySelectorAll(".thumbnail")
  let currentIndex = 0

  // Función para cambiar la imagen
  function changeImage(index) {
    if (index < 0) index = images.length - 1
    if (index >= images.length) index = 0

    currentIndex = index
    mainImg.src = images[index]

    // Actualizar miniaturas activas
    thumbnails.forEach((thumb, i) => {
      if (i === index) {
        thumb.classList.add("active")
      } else {
        thumb.classList.remove("active")
      }
    })
  }

  // Eventos de botones
  if (prevBtn) {
    prevBtn.addEventListener("click", (e) => {
      e.preventDefault()
      changeImage(currentIndex - 1)
    })
  }

  if (nextBtn) {
    nextBtn.addEventListener("click", (e) => {
      e.preventDefault()
      changeImage(currentIndex + 1)
    })
  }

  // Eventos de miniaturas
  thumbnails.forEach((thumb) => {
    thumb.addEventListener("click", function () {
      const index = Number.parseInt(this.getAttribute("data-index"), 10)
      changeImage(index)
    })
  })
}

function setupEvents() {
  // Eventos de búsqueda
  if (searchBtn) {
    searchBtn.addEventListener("click", searchProducts)
  }

  if (searchInput) {
    searchInput.addEventListener("keyup", (event) => {
      if (event.key === "Enter") {
        searchProducts()
      }
    })
  }

  if (clearSearchBtn) {
    clearSearchBtn.addEventListener("click", clearSearch)
  }

  // Evento para cargar más productos
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener("click", (e) => {
      e.preventDefault()
      loadMoreProducts()
    })
  }

  // Eventos del modal de checkout
  if (checkoutBtn) {
    checkoutBtn.addEventListener("click", () => {
      if (checkoutModal) checkoutModal.style.display = "block"
    })
  }

  if (closeModalBtn) {
    closeModalBtn.addEventListener("click", () => {
      if (checkoutModal) checkoutModal.style.display = "none"
    })
  }

  // Configurar el evento de envío del formulario de checkout
  if (checkoutForm) {
    checkoutForm.addEventListener("submit", processCheckout)
  }

  // Evento para cerrar el modal de producto
  if (productModalClose) {
    productModalClose.addEventListener("click", closeModal)
  }

  // Evento para el menú toggle
  if (menuToggle) {
    menuToggle.addEventListener("click", () => {
      if (menu) menu.classList.toggle("active")
    })
  }

  // Eventos para filtrar por categoría
  document.querySelectorAll("a[data-category]").forEach((link) => {
    link.addEventListener("click", function (e) {
      e.preventDefault()
      const category = this.getAttribute("data-category")
      console.log("Categoría seleccionada desde menú:", category)
      filterByCategory(category)
    })
  })

  // Eventos para filtrar por categoría (tarjetas de categoría)
  document.querySelectorAll(".category-card").forEach((card) => {
    card.addEventListener("click", function (e) {
      e.preventDefault()
      const category = this.getAttribute("data-category")
      console.log("Categoría seleccionada desde tarjeta:", category)
      filterByCategory(category)

      // Desplazarse a la sección de productos
      const productosSection = document.getElementById("productos")
      if (productosSection) {
        productosSection.scrollIntoView({ behavior: "smooth" })
      }
    })
  })

  // Iniciar slider automático
  setInterval(nextSlide, 5000) // Cambiar cada 5 segundos
}

