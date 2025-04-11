/**
 * Script para cargar los datos de envío en el checkout
 */
document.addEventListener("DOMContentLoaded", () => {
  // Cargar datos de envío desde localStorage
  const shippingCost = localStorage.getItem("bearShopShippingCost") || 0
  const freeShipping = localStorage.getItem("bearShopFreeShipping") === "true"
  const shippingOrigin = localStorage.getItem("bearShopShippingOrigin") || "Parque Chacabuco (1406)"
  const shippingProductName = localStorage.getItem("bearShopShippingProductName") || ""
  const shippingDeliveryTime = localStorage.getItem("bearShopShippingDeliveryTime") || ""
  const shippingAddressJson = localStorage.getItem("bearShopShippingAddress") || "{}"

  try {
    // Enviar datos al servidor para guardarlos en la sesión
    fetch("api/guardar-datos-envio.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        shipping_cost: shippingCost,
        free_shipping: freeShipping,
        shipping_origin: shippingOrigin,
        shipping_product_name: shippingProductName,
        shipping_delivery_time: shippingDeliveryTime,
        shipping_address: JSON.parse(shippingAddressJson),
      }),
    })
      .then((response) => {
        if (!response.ok) {
          console.error("Error al guardar datos de envío")
        }
      })
      .catch((error) => {
        console.error("Error:", error)
      })
  } catch (error) {
    console.error("Error al procesar datos de envío:", error)
  }
})
