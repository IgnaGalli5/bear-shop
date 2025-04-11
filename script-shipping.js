/**
 * Funciones para el cálculo de envío
 */

// Importar o declarar formatPrice y getCart (asumiendo que están en otro archivo o se definen globalmente)
function formatPrice(price) {
  return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".")
}

function getCart() {
  // Esta es una implementación de ejemplo. Deberías reemplazarla con tu lógica real para obtener el carrito.
  try {
    return JSON.parse(localStorage.getItem("bearShopCart")) || []
  } catch (e) {
    console.error("Error parsing cart from localStorage", e)
    return []
  }
}

// Función para calcular el subtotal del carrito
function calcularSubtotal(cart) {
  return cart.reduce((total, item) => total + item.price * item.quantity, 0)
}

// Función para verificar si califica para envío gratis
function calificaParaEnvioGratis(subtotal) {
  return subtotal >= 60000 // $60,000 para envío gratis
}

// Función para calcular el envío
async function calcularEnvio(cart, shippingAddress) {
  try {
    const response = await fetch("api/calcular-envio.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        cart: cart,
        shippingAddress: shippingAddress,
      }),
    })

    if (!response.ok) {
      throw new Error("Error al calcular el envío")
    }

    return await response.json()
  } catch (error) {
    console.error("Error:", error)
    return {
      success: false,
      freeShipping: false,
      shippingCost: 2500, // Costo por defecto en caso de error
      message: error.message,
      origin: "Parque Chacabuco (1406)",
    }
  }
}

// Función para actualizar el resumen del carrito con el costo de envío
function actualizarResumenConEnvio(shippingResult) {
  const subtotalElement = document.getElementById("cart-subtotal")
  const totalElement = document.getElementById("cart-total")
  const shippingElement = document.getElementById("cart-shipping")

  if (!subtotalElement || !totalElement) return

  // Obtener el subtotal actual sin formato
  const subtotalText = subtotalElement.textContent.replace("$", "").replace(/\./g, "").replace(",", ".")
  const subtotal = Number.parseFloat(subtotalText)

  // Datos del envío
  const shippingCost = shippingResult.shippingCost
  const isFreeShipping = shippingResult.freeShipping
  const origin = shippingResult.origin || "Parque Chacabuco (1406)"
  const deliveryTimeMin = shippingResult.deliveryTimeMin || ""
  const deliveryTimeMax = shippingResult.deliveryTimeMax || ""
  const productName = shippingResult.productName || "Correo Argentino"

  // Texto de tiempo de entrega
  let deliveryTimeText = ""
  if (deliveryTimeMin && deliveryTimeMax) {
    deliveryTimeText = ` (${deliveryTimeMin}-${deliveryTimeMax} días hábiles)`
  }

  // Crear o actualizar el elemento de envío
  if (!shippingElement) {
    const shippingDiv = document.createElement("div")
    shippingDiv.className = "summary-item"
    shippingDiv.innerHTML = `
            <span>Envío desde ${origin}${deliveryTimeText}:</span>
            <span id="cart-shipping">${isFreeShipping ? '<span class="free-shipping">Gratis</span>' : "$" + formatPrice(shippingCost)}</span>
        `

    // Insertar antes del total
    totalElement.parentNode.parentNode.insertBefore(shippingDiv, totalElement.parentNode)
  } else {
    // Actualizar el texto del elemento padre para incluir el origen y tiempo de entrega
    const shippingLabel = shippingElement.parentNode.querySelector("span:first-child")
    if (shippingLabel) {
      shippingLabel.textContent = `Envío desde ${origin}${deliveryTimeText}:`
    }

    shippingElement.innerHTML = isFreeShipping
      ? '<span class="free-shipping">Gratis</span>'
      : "$" + formatPrice(shippingCost)
  }

  // Calcular el total con envío
  const total = subtotal + (isFreeShipping ? 0 : shippingCost)

  // Actualizar el total
  totalElement.innerHTML = "$" + formatPrice(total)

  // Guardar la información de envío en localStorage para usarla en el checkout
  localStorage.setItem("bearShopShippingCost", shippingCost)
  localStorage.setItem("bearShopFreeShipping", isFreeShipping ? "true" : "false")
  localStorage.setItem("bearShopShippingOrigin", origin)
  localStorage.setItem("bearShopShippingProductName", productName)
  localStorage.setItem("bearShopShippingDeliveryTime", `${deliveryTimeMin}-${deliveryTimeMax}`)
}

// Función para mostrar el formulario de dirección de envío
function mostrarFormularioEnvio() {
  const cartSummary = document.getElementById("cart-summary")
  if (!cartSummary) return

  // Verificar si ya existe el formulario
  if (document.getElementById("shipping-form")) return

  // Obtener el carrito y calcular el subtotal
  const cart = getCart()
  const subtotal = calcularSubtotal(cart)
  const calificaEnvioGratis = calificaParaEnvioGratis(subtotal)

  // Mensaje de envío gratis si aplica
  const mensajeEnvioGratis = calificaEnvioGratis
    ? `<div class="free-shipping-message">
      <p>¡Felicidades! Tu compra califica para envío gratis.</p>
      <p>Igualmente necesitamos tus datos de envío para procesar tu pedido.</p>
     </div>`
    : ""

  const shippingFormHTML = `
        <div id="shipping-form" class="shipping-form">
            <h4>Datos de envío</h4>
            <p class="shipping-origin">Envío desde: Parque Chacabuco (CP: 1406)</p>
            ${mensajeEnvioGratis}
            <div class="form-group">
                <label for="shipping-address">Dirección</label>
                <input type="text" id="shipping-address" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="shipping-city">Ciudad</label>
                    <input type="text" id="shipping-city" required>
                </div>
                <div class="form-group">
                    <label for="shipping-zipcode">Código Postal</label>
                    <div class="input-with-link">
                        <input type="text" id="shipping-zipcode" required>
                        <a href="https://www.correoargentino.com.ar/formularios/cpa" target="_blank" class="zipcode-link">¿No conoces tu código postal?</a>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="shipping-province">Provincia</label>
                <select id="shipping-province" required>
                    <option value="">Seleccionar provincia</option>
                    <option value="C">Ciudad Autónoma de Buenos Aires</option>
                    <option value="B">Buenos Aires</option>
                    <option value="K">Catamarca</option>
                    <option value="H">Chaco</option>
                    <option value="U">Chubut</option>
                    <option value="X">Córdoba</option>
                    <option value="W">Corrientes</option>
                    <option value="E">Entre Ríos</option>
                    <option value="P">Formosa</option>
                    <option value="Y">Jujuy</option>
                    <option value="L">La Pampa</option>
                    <option value="F">La Rioja</option>
                    <option value="M">Mendoza</option>
                    <option value="N">Misiones</option>
                    <option value="Q">Neuquén</option>
                    <option value="R">Río Negro</option>
                    <option value="A">Salta</option>
                    <option value="J">San Juan</option>
                    <option value="D">San Luis</option>
                    <option value="Z">Santa Cruz</option>
                    <option value="S">Santa Fe</option>
                    <option value="G">Santiago del Estero</option>
                    <option value="V">Tierra del Fuego</option>
                    <option value="T">Tucumán</option>
                </select>
            </div>
            <div class="form-group">
                <label for="shipping-delivery-type">Tipo de entrega</label>
                <select id="shipping-delivery-type" required>
                    <option value="D">Entrega a domicilio</option>
                    <option value="S">Retiro en sucursal</option>
                </select>
            </div>
            <div id="shipping-agency-container" class="form-group" style="display: none;">
                <label for="shipping-agency">Sucursal</label>
                <select id="shipping-agency" disabled>
                    <option value="">Seleccione una provincia primero</option>
                </select>
            </div>
            <button type="button" id="calculate-shipping" class="btn-small">Calcular envío</button>
            <div id="shipping-result" class="shipping-result"></div>
        </div>
    `

  // Insertar el formulario antes de las opciones de pago
  const paymentMethods = document.querySelector(".payment-methods")
  if (paymentMethods) {
    paymentMethods.insertAdjacentHTML("beforebegin", shippingFormHTML)

    // Mostrar/ocultar selector de sucursal según el tipo de entrega
    document.getElementById("shipping-delivery-type").addEventListener("change", function () {
      const agencyContainer = document.getElementById("shipping-agency-container")
      if (this.value === "S") {
        agencyContainer.style.display = "block"
        cargarSucursales()
      } else {
        agencyContainer.style.display = "none"
      }
    })

    // Cargar sucursales cuando cambia la provincia
    document.getElementById("shipping-province").addEventListener("change", () => {
      if (document.getElementById("shipping-delivery-type").value === "S") {
        cargarSucursales()
      }
    })

    // Agregar evento al botón de calcular envío
    document.getElementById("calculate-shipping").addEventListener("click", async () => {
      const address = document.getElementById("shipping-address").value
      const city = document.getElementById("shipping-city").value
      const zipCode = document.getElementById("shipping-zipcode").value
      const province = document.getElementById("shipping-province").value
      const deliveryType = document.getElementById("shipping-delivery-type").value
      let agency = null

      if (deliveryType === "S") {
        agency = document.getElementById("shipping-agency").value
        if (!agency) {
          document.getElementById("shipping-result").innerHTML =
            '<p class="error-message">Por favor selecciona una sucursal</p>'
          return
        }
      }

      if (!address || !city || !zipCode || !province) {
        document.getElementById("shipping-result").innerHTML =
          '<p class="error-message">Por favor completa todos los campos</p>'
        return
      }

      // Mostrar cargando
      document.getElementById("shipping-result").innerHTML =
        '<p class="loading-message">Calculando costo de envío...</p>'

      // Obtener carrito
      const cart = getCart()

      // Calcular envío
      const shippingResult = await calcularEnvio(cart, {
        streetName: address,
        cityName: city,
        zipCode: zipCode,
        state: province,
        deliveryType: deliveryType,
        agency: agency,
      })

      // Mostrar resultado
      if (shippingResult.success) {
        let messageText = shippingResult.message

        // Agregar información de tiempo de entrega si está disponible
        if (shippingResult.deliveryTimeMin && shippingResult.deliveryTimeMax) {
          messageText += ` (Tiempo estimado de entrega: ${shippingResult.deliveryTimeMin}-${shippingResult.deliveryTimeMax} días hábiles)`
        }

        document.getElementById("shipping-result").innerHTML = `
                    <p class="success-message">${messageText}</p>
                    <p class="shipping-details">Servicio: ${shippingResult.productName || "Correo Argentino"}</p>
                `

        // Actualizar resumen del carrito
        actualizarResumenConEnvio(shippingResult)

        // Guardar dirección en localStorage
        localStorage.setItem(
          "bearShopShippingAddress",
          JSON.stringify({
            streetName: address,
            cityName: city,
            zipCode: zipCode,
            state: province,
            deliveryType: deliveryType,
            agency: agency,
          }),
        )

        // Habilitar botón de checkout
        const checkoutBtn = document.getElementById("checkout-btn")
        if (checkoutBtn) {
          checkoutBtn.disabled = false
          checkoutBtn.title = "Proceder al pago"
        }
      } else {
        document.getElementById("shipping-result").innerHTML = `
                    <p class="error-message">${shippingResult.message}</p>
                `
      }
    })
  }
}

// Función para cargar sucursales
async function cargarSucursales() {
  const provinceSelect = document.getElementById("shipping-province")
  const agencySelect = document.getElementById("shipping-agency")

  if (!provinceSelect || !agencySelect) return

  const province = provinceSelect.value

  if (!province) {
    agencySelect.innerHTML = '<option value="">Seleccione una provincia primero</option>'
    agencySelect.disabled = true
    return
  }

  // Mostrar cargando
  agencySelect.innerHTML = '<option value="">Cargando sucursales...</option>'
  agencySelect.disabled = true

  try {
    const response = await fetch(`api/obtener-sucursales.php?province=${province}`)

    if (!response.ok) {
      throw new Error("Error al obtener sucursales")
    }

    const data = await response.json()

    if (data.success && data.agencies && data.agencies.length > 0) {
      // Llenar el select con las sucursales
      agencySelect.innerHTML = '<option value="">Seleccione una sucursal</option>'

      data.agencies.forEach((agency) => {
        const option = document.createElement("option")
        option.value = agency.code
        option.textContent = `${agency.name} - ${agency.location.address.streetName} ${agency.location.address.streetNumber}`
        agencySelect.appendChild(option)
      })

      agencySelect.disabled = false
    } else {
      agencySelect.innerHTML = '<option value="">No hay sucursales disponibles</option>'
      agencySelect.disabled = true
    }
  } catch (error) {
    console.error("Error:", error)
    agencySelect.innerHTML = '<option value="">Error al cargar sucursales</option>'
    agencySelect.disabled = true
  }
}

// Función para inicializar el cálculo de envío
function inicializarCalculoEnvio() {
  // Verificar si hay items en el carrito
  const cart = getCart()
  if (cart.length === 0) return

  // Mostrar formulario de envío
  mostrarFormularioEnvio()

  // Deshabilitar botón de checkout hasta que se calcule el envío
  const checkoutBtn = document.getElementById("checkout-btn")
  if (checkoutBtn) {
    checkoutBtn.disabled = true
    checkoutBtn.title = "Calcula el costo de envío primero"
  }
}

// Inicializar cuando el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
  // Verificar si estamos en la página del carrito
  const cartSection = document.getElementById("carrito")
  if (cartSection) {
    // Observar cambios en el carrito
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        if (mutation.type === "childList" && document.getElementById("cart-summary")) {
          inicializarCalculoEnvio()
          observer.disconnect()
        }
      })
    })

    observer.observe(cartSection, { childList: true, subtree: true })
  }
})
