document.addEventListener("DOMContentLoaded", () => {
  // Datos de productos con información detallada
  // Cada producto tiene un ID único, nombre, precio, información de cuotas, imagen, categoría,
  // descripción, características, modo de uso y calificaciones
  const products = [
    {
      id: 1,
      name: "ARENCIA - Holy Hyssop Serum 30 50ml",
      price: 28125,
      installments: 3,
      installmentPrice: 9375,
      image: "productos/arencia.jpg",
      category: "skincare",
      description:
        "Un sérum facial de alta potencia formulado con extracto de hisopo sagrado, conocido por sus propiedades calmantes y regeneradoras. Ideal para pieles sensibles o con rojeces.",
      features: [
        "Contiene 30% de extracto de hisopo sagrado",
        "Libre de parabenos y fragancias artificiales",
        "Textura ligera de rápida absorción",
        "Hidrata profundamente sin dejar sensación grasa",
      ],
      usage:
        "Aplicar 2-3 gotas sobre el rostro limpio y seco, por la mañana y noche. Masajear suavemente hasta su completa absorción. Seguir con crema hidratante.",
      rating: 4.7,
      ratingCount: 156,
    },
    {
      id: 2,
      name: "PERIPERA - Ink Black Cara #02",
      price: 31680,
      installments: 3,
      installmentPrice: 10560,
      image: "productos/peripera.jpg",
      category: "maquillaje",
      description:
        "Máscara de pestañas de larga duración que proporciona volumen y longitud extraordinarios. Su fórmula resistente al agua garantiza un look impecable durante todo el día.",
      features: [
        "Color negro intenso que no se desvanece",
        "Fórmula resistente al agua y la humedad",
        "Cepillo de precisión para una aplicación sin grumos",
        "No irrita los ojos sensibles",
      ],
      usage:
        "Aplicar desde la raíz hasta las puntas con movimientos zigzagueantes. Para mayor volumen, aplicar una segunda capa después de que la primera se haya secado ligeramente.",
      rating: 4.8,
      ratingCount: 203,
    },
    {
      id: 3,
      name: "PERIPERA - Ink Mood Matte Stick",
      price: 18090,
      installments: 3,
      installmentPrice: 6030,
      image: "productos/peripera3.jpg",
      category: "maquillaje",
      description:
        "Labial en barra con acabado mate aterciopelado que proporciona un color intenso y duradero. Su fórmula hidratante evita la sensación de sequedad típica de los labiales mate.",
      features: [
        "Acabado mate sin resecar los labios",
        "Duración de hasta 8 horas",
        "Contiene aceites naturales hidratantes",
        "Disponible en tonos vibrantes y naturales",
      ],
      usage:
        "Aplicar directamente sobre los labios limpios. Para mayor precisión, utilizar un pincel de labios. Puede aplicarse una capa de bálsamo labial previamente para mayor hidratación.",
      rating: 4.5,
      ratingCount: 178,
    },
    {
      id: 4,
      name: "PERIPERA - Speedy Skinny Brow",
      price: 18090,
      installments: 3,
      installmentPrice: 6030,
      image: "productos/peripera4.jpg",
      category: "maquillaje",
      description:
        "Lápiz de cejas ultrafino que permite dibujar trazos precisos que imitan el aspecto natural del vello. Ideal para rellenar espacios vacíos y definir la forma de las cejas.",
      features: [
        "Punta de 1.5mm para trazos ultraprecisos",
        "Fórmula resistente al agua y la transpiración",
        "Incluye cepillo para difuminar",
        "Disponible en 5 tonos naturales",
      ],
      usage:
        "Dibujar trazos finos siguiendo la dirección natural del crecimiento del vello. Difuminar con el cepillo incluido para un acabado natural.",
      rating: 4.6,
      ratingCount: 142,
    },
    {
      id: 5,
      name: "ETUDE - Fixing Tint #01 Dusty Beige",
      price: 22500,
      installments: 3,
      installmentPrice: 7500,
      image: "productos/peripera.jpg",
      category: "maquillaje",
      description:
        "Tinte labial de larga duración con acabado natural. Su fórmula ligera se funde con los labios creando un efecto de color natural que dura todo el día sin transferirse.",
      features: [
        "Efecto tinte que no se transfiere",
        "Resistente al agua y a las comidas",
        "Fórmula hidratante con extracto de granada",
        "Tono beige polvoriento versátil para cualquier look",
      ],
      usage:
        "Aplicar una capa fina en el centro de los labios y difuminar hacia los bordes con el dedo o un pincel. Esperar 30 segundos para que se fije completamente.",
      rating: 4.4,
      ratingCount: 167,
    },
    {
      id: 6,
      name: "ETUDE - Play Color Eyes #Wine Party",
      price: 35700,
      installments: 3,
      installmentPrice: 11900,
      image: "productos/peripera3.jpg",
      category: "maquillaje",
      description:
        "Paleta de sombras de ojos con 10 tonos inspirados en los colores del vino. Incluye acabados mate, satinados y metálicos para crear múltiples looks desde los más naturales hasta los más dramáticos.",
      features: [
        "10 tonos complementarios en la gama de los burdeos y marrones",
        "Alta pigmentación y fácil difuminado",
        "Fórmula duradera sin caída de producto",
        "Incluye espejo y aplicador doble",
      ],
      usage:
        "Aplicar los tonos más claros como base y en el arco de la ceja. Utilizar los tonos medios en el párpado móvil y los más oscuros para definir. Los tonos metálicos pueden aplicarse con el dedo para mayor intensidad.",
      rating: 4.9,
      ratingCount: 215,
    },
    {
      id: 7,
      name: "MISSHA - Time Revolution Essence 150ml",
      price: 42300,
      installments: 3,
      installmentPrice: 14100,
      image: "productos/arencia.jpg",
      category: "skincare",
      description:
        "Esencia facial fermentada que mejora visiblemente la textura y luminosidad de la piel. Su fórmula concentrada con más de 90% de ingredientes fermentados promueve la renovación celular y combate los signos del envejecimiento.",
      features: [
        "Contiene extracto de levadura fermentada Saccharomyces",
        "Mejora la elasticidad y firmeza de la piel",
        "Reduce la apariencia de manchas y decoloraciones",
        "Textura ligera que penetra rápidamente en la piel",
      ],
      usage:
        "Después de limpiar y tonificar, verter una pequeña cantidad en la palma de la mano. Aplicar suavemente sobre el rostro con movimientos de presión. Continuar con el resto de la rutina de cuidado facial.",
      rating: 4.8,
      ratingCount: 289,
    },
    {
      id: 8,
      name: "MISSHA - Super Aqua Cell Renew Snail Cream",
      price: 29700,
      installments: 3,
      installmentPrice: 9900,
      image: "productos/peripera4.jpg",
      category: "skincare",
      description:
        "Crema facial intensamente hidratante y reparadora formulada con 70% de extracto de baba de caracol. Ayuda a regenerar la piel dañada, reduce cicatrices y marcas de acné, y proporciona una hidratación duradera.",
      features: [
        "70% de filtrado de mucina de caracol",
        "Complejo de 5 tipos de ácido hialurónico",
        "Efecto calmante inmediato para pieles irritadas",
        "Textura ligera que no deja residuo graso",
      ],
      usage:
        "Aplicar una cantidad generosa sobre el rostro limpio y seco, después del sérum. Masajear suavemente con movimientos ascendentes hasta su completa absorción. Usar mañana y noche.",
      rating: 4.7,
      ratingCount: 176,
    },
    {
      id: 9,
      name: "COSRX - Advanced Snail 96 Mucin Power Essence",
      price: 33000,
      installments: 3,
      installmentPrice: 11000,
      image: "productos/arencia.jpg",
      category: "skincare",
      description:
        "Esencia concentrada con 96% de filtrado de mucina de caracol que repara, hidrata y calma la piel. Su fórmula ligera pero potente mejora la elasticidad, reduce cicatrices y proporciona una hidratación profunda.",
      features: [
        "96% de filtrado de mucina de caracol",
        "Libre de fragancias y alcohol",
        "Adecuado para pieles sensibles y con acné",
        "Textura viscosa que se absorbe rápidamente",
      ],
      usage:
        "Aplicar 2-3 gotas sobre el rostro limpio y ligeramente húmedo. Dar palmaditas suaves hasta su completa absorción. Puede usarse mañana y noche antes de la crema hidratante.",
      rating: 4.9,
      ratingCount: 312,
    },
    {
      id: 10,
      name: "COSRX - Low pH Good Morning Gel Cleanser",
      price: 19800,
      installments: 3,
      installmentPrice: 6600,
      image: "productos/peripera.jpg",
      category: "skincare",
      description:
        "Limpiador facial en gel con pH bajo (5.0-6.0) que limpia suavemente sin alterar la barrera natural de la piel. Formulado con extracto de té y ácido betaína salicílico para una limpieza profunda pero no agresiva.",
      features: [
        "pH bajo similar al de la piel sana",
        "Contiene BHA natural para exfoliación suave",
        "Extracto de té para propiedades antioxidantes",
        "Fórmula suave adecuada para uso diario",
      ],
      usage:
        "Aplicar una pequeña cantidad sobre el rostro húmedo. Masajear suavemente en movimientos circulares, evitando el contorno de ojos. Enjuagar abundantemente con agua tibia. Ideal para uso matutino.",
      rating: 4.6,
      ratingCount: 245,
    },
    {
      id: 11,
      name: "SOME BY MI - AHA-BHA-PHA 30 Days Miracle Serum",
      price: 27900,
      installments: 3,
      installmentPrice: 9300,
      image: "productos/peripera3.jpg",
      category: "skincare",
      description:
        "Sérum exfoliante con triple acción que combina AHA, BHA y PHA para una renovación celular completa. Formulado con extracto de árbol de té al 14.5% que purifica y calma la piel problemática.",
      features: [
        "Combinación de ácidos AHA, BHA y PHA para exfoliación completa",
        "14.5% de extracto de árbol de té con propiedades antibacterianas",
        "Contiene centella asiática para calmar la piel",
        "Resultados visibles en 30 días de uso continuo",
      ],
      usage:
        "Aplicar 2-3 gotas sobre el rostro limpio y seco, evitando el contorno de ojos. Comenzar con aplicaciones en días alternos para que la piel se acostumbre. Usar protector solar durante el día.",
      rating: 4.7,
      ratingCount: 198,
    },
    {
      id: 12,
      name: "SOME BY MI - Snail Truecica Miracle Repair Cream",
      price: 31500,
      installments: 3,
      installmentPrice: 10500,
      image: "productos/peripera4.jpg",
      category: "skincare",
      description:
        "Crema reparadora intensiva que combina extracto de baba de caracol y complejo Truecica para calmar y regenerar la piel dañada o con imperfecciones. Ideal para pieles sensibles, con acné o rojeces.",
      features: [
        "Contiene 70% de filtrado de mucina de caracol",
        "Complejo Truecica (árbol de té, centella y cica) para calmar la piel",
        "Fórmula hipoalergénica testada dermatológicamente",
        "Textura ligera no comedogénica",
      ],
      usage:
        "Aplicar una cantidad generosa sobre el rostro limpio, después del sérum. Masajear suavemente hasta su completa absorción. Usar mañana y noche como último paso de la rutina.",
      rating: 4.8,
      ratingCount: 167,
    },
    {
      id: 13,
      name: "LANEIGE - Water Sleeping Mask 70ml",
      price: 39600,
      installments: 3,
      installmentPrice: 13200,
      image: "productos/arencia.jpg",
      category: "skincare",
      description:
        "Mascarilla nocturna intensamente hidratante que trabaja mientras duermes para revitalizar la piel cansada. Su fórmula con tecnología SLEEP-TOX™ purifica la piel del estrés acumulado durante el día.",
      features: [
        "Tecnología SLEEP-TOX™ para purificar la piel durante el sueño",
        "Complejo MOISTURE WRAP™ para hidratación duradera",
        "Aroma relajante que mejora la calidad del sueño",
        "Textura gel refrescante de rápida absorción",
      ],
      usage:
        "Aplicar una capa generosa como último paso de la rutina nocturna, después de la crema hidratante. Dejar actuar durante toda la noche y enjuagar por la mañana. Usar 2-3 veces por semana.",
      rating: 4.9,
      ratingCount: 278,
    },
    {
      id: 14,
      name: "LANEIGE - Lip Sleeping Mask 20g",
      price: 24750,
      installments: 3,
      installmentPrice: 8250,
      image: "productos/peripera.jpg",
      category: "accesorios",
      description:
        "Mascarilla labial nocturna que nutre, repara y suaviza los labios mientras duermes. Su fórmula con tecnología Berry Mix Complex™ está enriquecida con antioxidantes y vitamina C para unos labios suaves y jugosos.",
      features: [
        "Berry Mix Complex™ rico en antioxidantes",
        "Tecnología Moisture Wrap™ para hidratación duradera",
        "Elimina células muertas para unos labios suaves",
        "Incluye aplicador de silicona",
      ],
      usage:
        "Aplicar una capa generosa sobre los labios antes de dormir utilizando el aplicador incluido. Dejar actuar durante toda la noche. Por la mañana, retirar el exceso con un pañuelo o lavar suavemente.",
      rating: 4.9,
      ratingCount: 345,
    },
    {
      id: 15,
      name: "INNISFREE - Green Tea Seed Serum 80ml",
      price: 36300,
      installments: 3,
      installmentPrice: 12100,
      image: "productos/peripera3.jpg",
      category: "skincare",
      description:
        "Sérum hidratante formulado con extracto de té verde de la isla de Jeju y aceite de semilla de té verde. Proporciona hidratación duradera y fortalece la barrera cutánea para una piel más saludable y radiante.",
      features: [
        "Extracto de té verde orgánico de Jeju",
        "Tecnología Dual-Moisture-Rising™",
        "Textura ligera de rápida absorción",
        "Aroma natural refrescante",
      ],
      usage:
        "Aplicar 2-3 gotas sobre el rostro limpio y tonificado. Dar palmaditas suaves hasta su completa absorción. Usar mañana y noche antes de la crema hidratante.",
      rating: 4.7,
      ratingCount: 256,
    },
    {
      id: 16,
      name: "INNISFREE - No Sebum Mineral Powder",
      price: 15900,
      installments: 3,
      installmentPrice: 5300,
      image: "productos/peripera4.jpg",
      category: "maquillaje",
      description:
        "Polvo mineral matificante que controla el exceso de grasa y minimiza la apariencia de los poros. Su fórmula ligera con minerales de la isla de Jeju absorbe el sebo sin resecar la piel.",
      features: [
        "Contiene minerales de la isla de Jeju",
        "Extracto de menta para un efecto refrescante",
        "Acabado mate natural sin efecto blanquecino",
        "Compacto y perfecto para retoques durante el día",
      ],
      usage:
        "Aplicar con la borla incluida sobre las zonas propensas a brillos, como la zona T. Puede usarse sobre el maquillaje para fijarlo o solo sobre la piel para un acabado mate natural.",
      rating: 4.8,
      ratingCount: 312,
    },
    {
      id: 17,
      name: "KLAIRS - Supple Preparation Facial Toner",
      price: 26400,
      installments: 3,
      installmentPrice: 8800,
      image: "productos/arencia.jpg",
      category: "skincare",
      description:
        "Tónico facial hidratante y calmante formulado para pieles sensibles. Equilibra el pH de la piel, hidrata profundamente y prepara la piel para una mejor absorción de los productos siguientes.",
      features: [
        "pH balanceado (5.5) similar al de la piel sana",
        "Sin alcohol ni ingredientes irritantes",
        "Contiene extracto de centella asiática para calmar la piel",
        "Textura ligeramente viscosa para mayor hidratación",
      ],
      usage:
        "Después de limpiar el rostro, aplicar unas gotas en la palma de la mano o en un algodón. Dar palmaditas suaves sobre el rostro hasta su completa absorción. Usar mañana y noche.",
      rating: 4.8,
      ratingCount: 289,
    },
    {
      id: 18,
      name: "KLAIRS - Midnight Blue Calming Cream",
      price: 32100,
      installments: 3,
      installmentPrice: 10700,
      image: "productos/peripera.jpg",
      category: "skincare",
      description:
        "Crema calmante intensiva para pieles sensibles, irritadas o con rojeces. Su fórmula con guaiazuleno y extracto de centella asiática reduce la inflamación y repara la barrera cutánea dañada.",
      features: [
        "Contiene guaiazuleno (pigmento azul natural) con propiedades antiinflamatorias",
        "Extracto de centella asiática para regeneración",
        "Fórmula sin alcohol, fragancias ni aceites esenciales",
        "Color azul natural sin colorantes artificiales",
      ],
      usage:
        "Aplicar una pequeña cantidad sobre las zonas irritadas o en todo el rostro después del sérum. Para mayor efecto calmante, guardar en el refrigerador antes de usar.",
      rating: 4.7,
      ratingCount: 198,
    },
    {
      id: 19,
      name: "PURITO - Centella Green Level Unscented Sun",
      price: 23100,
      installments: 3,
      installmentPrice: 7700,
      image: "productos/peripera3.jpg",
      category: "accesorios",
      description:
        "Protector solar de amplio espectro SPF50+ PA++++ formulado especialmente para pieles sensibles. Su fórmula sin fragancia con extracto de centella asiática protege y calma la piel al mismo tiempo.",
      features: [
        "SPF50+ PA++++ para protección UVA y UVB",
        "Fórmula sin fragancia ideal para pieles sensibles",
        "Contiene 70% de extracto de centella asiática",
        "Textura ligera de rápida absorción sin residuo blanco",
      ],
      usage:
        "Aplicar generosamente como último paso de la rutina de cuidado matutina, al menos 15 minutos antes de la exposición solar. Reaplicar cada 2 horas, especialmente después de nadar o sudar.",
      rating: 4.9,
      ratingCount: 267,
    },
    {
      id: 20,
      name: "PURITO - Fermented Complex 94 Boosting Essence",
      price: 28800,
      installments: 3,
      installmentPrice: 9600,
      image: "productos/peripera4.jpg",
      category: "accesorios",
      description:
        "Esencia facial con 94% de ingredientes fermentados que mejora la elasticidad y luminosidad de la piel. Los ingredientes fermentados penetran más profundamente en la piel para una hidratación y nutrición intensas.",
      features: [
        "94% de ingredientes fermentados de alta calidad",
        "Complejo de 3 probióticos para fortalecer la barrera cutánea",
        "Libre de alcohol, parabenos y fragancias artificiales",
        "Textura ligera que se absorbe rápidamente",
      ],
      usage:
        "Aplicar unas gotas sobre el rostro limpio y tonificado. Dar palmaditas suaves hasta su completa absorción. Usar mañana y noche antes del sérum o crema hidratante.",
      rating: 4.6,
      ratingCount: 178,
    },
  ]

  // Variables globales para el funcionamiento de la tienda
  let cart = [] // Carrito de compras
  let visibleProducts = 8 // Número inicial de productos visibles
  let filteredProducts = [...products] // Productos filtrados por búsqueda
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
  const productModalClose = productModal.querySelector(".close")
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

  // Renderizar productos: muestra los productos en la grilla
  function renderProducts() {
    productGrid.innerHTML = ""

    const productsToShow = filteredProducts.slice(0, visibleProducts)

    productsToShow.forEach((product) => {
      const productCard = document.createElement("div")
      productCard.className = "product-card"
      productCard.setAttribute("data-id", product.id)
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
              `
      productGrid.appendChild(productCard)
    })

    // Mostrar u ocultar el botón "Ver más" según la cantidad de productos
    if (filteredProducts.length <= visibleProducts) {
      loadMoreBtn.style.display = "none"
    } else {
      loadMoreBtn.style.display = "inline-block"
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
    const product = products.find((p) => p.id === productId)

    if (!product) return

    currentProductId = productId

    // Actualizar contenido del modal
    modalProductName.textContent = product.name
    modalProductPrice.textContent = `${formatPrice(product.price)} | ${product.installments} cuotas sin interés de ${formatPrice(product.installmentPrice)}`
    modalProductDescription.textContent = product.description
    modalProductUsage.textContent = product.usage
    modalRatingCount.textContent = `(${product.ratingCount} reseñas)`

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
    modalProductFeatures.innerHTML = ""
    product.features.forEach((feature) => {
      const li = document.createElement("li")
      li.textContent = feature
      modalProductFeatures.appendChild(li)
    })

    // Actualizar imagen
    modalProductImage.innerHTML = `<img src="${product.image}" alt="${product.name}">`

    // Resetear cantidad
    modalQuantity.value = 1

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
    const query = searchInput.value.trim().toLowerCase()

    if (query === "") {
      filteredProducts = [...products]
      filterResults.style.display = "none"
    } else {
      filteredProducts = products.filter(
        (product) =>
          product.name.toLowerCase().includes(query) ||
          product.category.toLowerCase().includes(query) ||
          product.description.toLowerCase().includes(query),
      )

      searchTerm.textContent = `"${query}"`
      filterResults.style.display = "flex"
    }

    visibleProducts = 8
    renderProducts()
  }

  // Limpiar búsqueda: restablece los productos mostrados
  function clearSearch() {
    searchInput.value = ""
    filteredProducts = [...products]
    filterResults.style.display = "none"
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

    updateCart()
    showNotification(`${product.name} agregado al carrito`)
  }

  // Actualizar carrito: actualiza la visualización del carrito y los totales
  function updateCart() {
    // Actualizar contador de carrito
    const totalItems = cart.reduce((total, item) => total + item.quantity, 0)
    cartCount.textContent = totalItems

    // Si el carrito está vacío
    if (cart.length === 0) {
      emptyCartMessage.style.display = "block"
      cartSummary.style.display = "none"
      return
    }

    // Ocultar mensaje de carrito vacío y mostrar resumen
    emptyCartMessage.style.display = "none"
    cartSummary.style.display = "block"

    // Renderizar items del carrito
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
    cartSubtotal.textContent = formatPrice(subtotal)
    cartTotal.textContent = formatPrice(subtotal)

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
    if (cart.length === 0) return

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
    checkoutModal.style.display = "none"
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
    // Ocultar todas las diapositivas
    slides.forEach((slide) => {
      slide.classList.remove("active")
    })

    // Desactivar todos los indicadores
    indicatorDots.forEach((dot) => {
      dot.classList.remove("active")
    })

    // Mostrar la diapositiva actual
    slides[index].classList.add("active")

    // Activar el indicador correspondiente
    indicatorDots[index].classList.add("active")

    // Actualizar el índice actual
    currentSlide = index
  }

  // Función para avanzar al siguiente slide
  function nextSlide() {
    let next = currentSlide + 1
    if (next >= slides.length) {
      next = 0
    }
    showSlide(next)
  }

  // Iniciar el slider automático
  function startSlider() {
    if (slides.length > 0) {
      // Mostrar el primer slide
      showSlide(0)

      // Configurar el intervalo para cambiar automáticamente
      setInterval(nextSlide, 3000)
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
    if (!event.target.closest(".menu") && !event.target.closest("#menuToggle") && menu.classList.contains("active")) {
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
      checkoutModal.style.display = "none"
    })
  }

  // Cerrar modal de producto
  if (productModalClose) {
    productModalClose.addEventListener("click", () => {
      productModal.style.display = "none"
    })
  }

  // Cerrar modales al hacer clic fuera
  window.addEventListener("click", (event) => {
    if (event.target === checkoutModal) {
      checkoutModal.style.display = "none"
    }
    if (event.target === productModal) {
      productModal.style.display = "none"
    }
  })

  // Enviar pedido
  if (checkoutForm) {
    checkoutForm.addEventListener("submit", submitOrder)
  }

  // Control de cantidad en el modal de producto
  if (modalQuantityDecrease) {
    modalQuantityDecrease.addEventListener("click", () => {
      const quantity = Number.parseInt(modalQuantity.value)
      if (quantity > 1) {
        modalQuantity.value = quantity - 1
      }
    })
  }

  if (modalQuantityIncrease) {
    modalQuantityIncrease.addEventListener("click", () => {
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
      if (currentProductId) {
        const quantity = Number.parseInt(modalQuantity.value)
        addToCart(currentProductId, quantity)
        productModal.style.display = "none"
      }
    })
  }

  // Agregar evento para las tarjetas de categoría
  document.querySelectorAll(".category-card").forEach((card) => {
    card.addEventListener("click", function () {
      const category = this.getAttribute("data-category")
      searchInput.value = category
      searchProducts()

      // Scroll a la sección de productos
      document.querySelector("#productos").scrollIntoView({ behavior: "smooth" })
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

  // Inicializar
  renderProducts()
  updateCart()
  startSlider() // Iniciar el slider de novedades

  // Agregar función para guardar el carrito en localStorage
  function saveCart() {
    localStorage.setItem("bearShopCart", JSON.stringify(cart))
  }

  // Cargar carrito desde localStorage al inicio
  function loadCart() {
    const storedCart = localStorage.getItem("bearShopCart")
    if (storedCart) {
      cart = JSON.parse(storedCart)
      updateCart()
    }
  }

  loadCart()

  // Agregar delegación de eventos para los botones de cantidad en el carrito
  // Usar la variable cartItemsContainer ya declarada anteriormente
  // const cartItemsContainer = document.getElementById('cart-items');
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

  // Cargar carrito desde localStorage al iniciar
  const savedCart = localStorage.getItem("bearShopCart")
  if (savedCart) {
    try {
      cart = JSON.parse(savedCart)
      updateCart()
    } catch (e) {
      console.error("Error parsing saved cart:", e)
    }
  }
})

// Añadir botón de cierre flotante para móviles
document.addEventListener("DOMContentLoaded", () => {
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
      productModal.style.display = "none"
      this.style.display = "none"
    })

    return mobileCloseBtn
  }

  const mobileCloseBtn = createMobileCloseButton()

  // Modificar la función de abrir modal para mostrar el botón flotante en móviles
  const originalOpenProductModal = openProductModal

  // Reemplazar la función original con una versión mejorada
  openProductModal = (productId) => {
    originalOpenProductModal(productId)

    // Mostrar botón flotante en móviles si la pantalla es pequeña
    if (window.innerWidth <= 768 && mobileCloseBtn) {
      mobileCloseBtn.style.display = "flex"
    }
  }

  // Ocultar botón flotante cuando se cierra el modal
  const originalCloseModal = () => {
    productModal.style.display = "none"
    if (mobileCloseBtn) {
      mobileCloseBtn.style.display = "none"
    }
  }

  // Reemplazar eventos de cierre existentes
  if (productModalClose) {
    productModalClose.addEventListener("click", originalCloseModal)
  }

  // Actualizar el evento de clic fuera del modal
  window.addEventListener("click", (event) => {
    if (event.target === productModal) {
      originalCloseModal()
    }
    if (event.target === checkoutModal) {
      checkoutModal.style.display = "none"
    }
  })

  // Ajustar cuando cambia el tamaño de la ventana
  window.addEventListener("resize", () => {
    if (productModal.style.display === "block") {
      if (window.innerWidth <= 768 && mobileCloseBtn) {
        mobileCloseBtn.style.display = "flex"
      } else if (mobileCloseBtn) {
        mobileCloseBtn.style.display = "none"
      }
    }
  })
})

