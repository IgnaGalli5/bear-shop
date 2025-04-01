// Manejo del submenú - Versión simplificada para todos los dispositivos
document.addEventListener("DOMContentLoaded", () => {
    console.log("Script de submenú cargado - Versión unificada")
  
    // 1. MANEJO DEL SUBMENÚ
  
    // Obtener todos los elementos relevantes
    const productosMenuItem = document.querySelector(".has-submenu > a")
    const submenuToggles = document.querySelectorAll(".submenu-toggle")
    const submenus = document.querySelectorAll(".submenu")
  
    console.log("Elementos encontrados:", {
      productosMenuItem: !!productosMenuItem,
      toggles: submenuToggles.length,
      submenus: submenus.length,
    })
  
    // Función para mostrar/ocultar el submenú
    function toggleSubmenu(e) {
      e.preventDefault()
      e.stopPropagation()
  
      console.log("Toggle activado")
  
      // Encontrar el toggle y el submenú relacionado
      const toggle = this.classList.contains("submenu-toggle") ? this : this.nextElementSibling
  
      const submenu = toggle.nextElementSibling
  
      // Verificar que tenemos los elementos correctos
      if (!toggle || !submenu) {
        console.error("No se encontró el toggle o el submenú")
        return
      }
  
      console.log("Elementos para toggle:", {
        toggle: toggle,
        submenu: submenu,
      })
  
      // Alternar clases y estilos
      toggle.classList.toggle("active")
      submenu.classList.toggle("active")
  
      // Forzar el estilo display
      if (submenu.classList.contains("active")) {
        submenu.style.display = "block"
        console.log("Submenú mostrado")
      } else {
        submenu.style.display = "none"
        console.log("Submenú ocultado")
      }
    }
  
    // Agregar evento al elemento "Productos" y a los toggles
    if (productosMenuItem) {
      productosMenuItem.addEventListener("click", toggleSubmenu)
      console.log("Evento click agregado a Productos")
    }
  
    submenuToggles.forEach((toggle) => {
      toggle.addEventListener("click", toggleSubmenu)
      console.log("Evento click agregado a toggle")
    })
  
    // 2. FILTRADO DE PRODUCTOS POR CATEGORÍA
  
    const categoryLinks = document.querySelectorAll("a[data-category]")
    console.log("Links de categoría encontrados:", categoryLinks.length)
  
    categoryLinks.forEach((link) => {
      link.addEventListener("click", function (e) {
        e.preventDefault()
  
        const category = this.getAttribute("data-category")
        console.log("Categoría seleccionada:", category)
  
        // Cerrar el menú móvil si está abierto
        const menu = document.querySelector(".menu")
        if (menu && menu.classList.contains("active")) {
          menu.classList.remove("active")
        }
  
        // Cerrar todos los submenús
        submenus.forEach((submenu) => {
          submenu.classList.remove("active")
          submenu.style.display = "none"
        })
  
        submenuToggles.forEach((toggle) => {
          toggle.classList.remove("active")
        })
  
        // Filtrar productos
        filterProductsByCategory(category)
  
        // Desplazarse a la sección de productos
        const productosSection = document.getElementById("productos")
        if (productosSection) {
          productosSection.scrollIntoView({ behavior: "smooth" })
        }
      })
    })
  
    // Función para filtrar productos por categoría - MEJORADA
    function filterProductsByCategory(category) {
      console.log("Filtrando por categoría:", category)
  
      const productCards = document.querySelectorAll(".product-card")
      console.log("Productos encontrados:", productCards.length)
  
      if (!productCards.length) {
        console.warn("No se encontraron productos para filtrar")
        return
      }
  
      // Depurar las categorías disponibles
      const availableCategories = new Set()
      productCards.forEach((card) => {
        const cat = card.getAttribute("data-category")
        if (cat) availableCategories.add(cat.toLowerCase())
      })
      console.log("Categorías disponibles:", Array.from(availableCategories))
  
      // Normalizar la categoría buscada
      const normalizedCategory = category ? category.toLowerCase().trim() : null
      console.log("Categoría normalizada:", normalizedCategory)
  
      // Mostrar todos y luego filtrar
      let matchCount = 0
      productCards.forEach((card) => {
        // Primero mostrar todos
        card.style.display = "block"
  
        // Si hay una categoría seleccionada, filtrar
        if (normalizedCategory) {
          const productCategory = card.getAttribute("data-category")
          console.log(`Producto ID: ${card.getAttribute("data-id")}, Categoría: ${productCategory}`)
  
          // Normalizar la categoría del producto
          const normalizedProductCategory = productCategory ? productCategory.toLowerCase().trim() : ""
  
          // Verificar si coincide
          if (!normalizedProductCategory || normalizedProductCategory !== normalizedCategory) {
            card.style.display = "none"
          } else {
            matchCount++
          }
        }
      })
  
      console.log(`Se encontraron ${matchCount} productos que coinciden con la categoría ${normalizedCategory}`)
  
      // Actualizar el texto del filtro
      const filterResults = document.getElementById("filter-results")
      const searchTerm = document.getElementById("search-term")
  
      if (filterResults && searchTerm) {
        if (normalizedCategory) {
          filterResults.style.display = "flex"
          // Capitalizar primera letra
          const displayCategory = normalizedCategory.charAt(0).toUpperCase() + normalizedCategory.slice(1)
          searchTerm.textContent = `Categoría: ${displayCategory}`
        } else {
          filterResults.style.display = "none"
        }
      }
    }
  
    // 3. LIMPIAR BÚSQUEDA
  
    const clearSearchBtn = document.getElementById("clear-search")
    if (clearSearchBtn) {
      clearSearchBtn.addEventListener("click", () => {
        console.log("Limpiando búsqueda")
  
        // Limpiar filtros de categoría
        filterProductsByCategory(null)
  
        // Limpiar búsqueda
        const searchInput = document.getElementById("product-search")
        if (searchInput) {
          searchInput.value = ""
        }
  
        // Mostrar todos los productos
        const productCards = document.querySelectorAll(".product-card")
        productCards.forEach((card) => {
          card.style.display = "block"
        })
  
        // Ocultar el filtro
        const filterResults = document.getElementById("filter-results")
        if (filterResults) {
          filterResults.style.display = "none"
        }
      })
    }
  
    // 4. INICIALIZACIÓN
  
    // Ocultar todos los submenús al cargar
    submenus.forEach((submenu) => {
      submenu.style.display = "none"
      submenu.classList.remove("active")
    })
  
    console.log("Inicialización completada")
  
    // Depurar las categorías disponibles al inicio
    setTimeout(() => {
      const productCards = document.querySelectorAll(".product-card")
      const availableCategories = new Set()
      productCards.forEach((card) => {
        const cat = card.getAttribute("data-category")
        if (cat) availableCategories.add(cat.toLowerCase())
      })
      console.log("Categorías disponibles al inicio:", Array.from(availableCategories))
  
      // Verificar los enlaces de categoría
      const categoryLinks = document.querySelectorAll("a[data-category]")
      console.log("Enlaces de categoría:")
      categoryLinks.forEach((link) => {
        console.log(`- ${link.textContent}: ${link.getAttribute("data-category")}`)
      })
    }, 1000)
  })
  
  