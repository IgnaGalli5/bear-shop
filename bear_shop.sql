-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-03-2025 a las 20:33:20
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bear_shop`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_precios`
--

CREATE TABLE `historial_precios` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `tipo_accion` varchar(50) NOT NULL,
  `archivo_log` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `margenes_categoria`
--

CREATE TABLE `margenes_categoria` (
  `id` int(11) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `multiplicador` decimal(4,2) NOT NULL DEFAULT 2.00,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `margenes_categoria`
--

INSERT INTO `margenes_categoria` (`id`, `categoria`, `multiplicador`, `fecha_actualizacion`) VALUES
(1, 'accesorios', 2.00, '2025-03-26 22:33:01'),
(2, 'maquillaje', 2.00, '2025-03-26 22:15:00'),
(3, 'skincare', 2.00, '2025-03-26 22:15:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `fecha` datetime NOT NULL,
  `estado` varchar(50) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `direccion_envio` text DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `fecha`, `estado`, `total`, `metodo_pago`, `direccion_envio`, `notas`, `created_at`) VALUES
(1, 1, '2025-03-27 01:19:49', 'completado', 4500.00, 'efectivo', 'papa papa', 'papa@gmail.com', '2025-03-27 00:19:49');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `caracteristicas` text DEFAULT NULL,
  `modo_uso` text DEFAULT NULL,
  `calificacion` decimal(3,1) DEFAULT 5.0,
  `num_calificaciones` int(11) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `precio_costo` decimal(10,2) DEFAULT NULL,
  `multiplicador` decimal(4,2) DEFAULT 2.00,
  `precio_promocion` decimal(10,2) DEFAULT NULL,
  `promocion_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `precio`, `imagen`, `categoria`, `descripcion`, `caracteristicas`, `modo_uso`, `calificacion`, `num_calificaciones`, `fecha_creacion`, `precio_costo`, `multiplicador`, `precio_promocion`, `promocion_id`) VALUES
(2, 'Caja pads algodon suave sin pelusa 150pc #20082', 6200.00, 'productos/67e223e1602f4_cajaPadsAlgodon.jpg', 'skincare', 'Caja pads algodon suave sin pelusa 150pc #20082 - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 3100.00, 2.00, NULL, NULL),
(3, 'Caja x10u parches removedor puntos negros Ymxercos', 1800.00, 'productos/67e223af52c7f_parchesx10.jpg', 'skincare', 'Caja x10u parches removedor puntos negros Ymxercos - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 900.00, 2.00, NULL, NULL),
(4, 'Caja parche para acne 48pc Kormesic', 2700.00, 'productos/67e224076003e_cajaParcheACne.jpeg', 'skincare', 'Caja parche para acne 48pc Kormesic - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 1350.00, 2.00, NULL, NULL),
(5, 'Kit ice globes 2pc City girls rosa/celeste', 10000.00, 'productos/67e223805b7ad_kitIceGlobes.jpeg', 'skincare', 'Kit ice globes 2pc City girls rosa/celeste - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 5000.00, 2.00, NULL, NULL),
(6, 'Esponja celulosa City Girls pack x2u', 1400.00, 'productos/67e223557ccf8_esponjaCelulosaCity.jpeg', 'skincare', 'Esponja celulosa City Girls pack x2u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 700.00, 2.00, NULL, NULL),
(7, 'Esponja celulosa limpieza facial pack 2u', 1400.00, 'productos/67e2236c8581f_esponjaFacial.jpeg', 'skincare', 'Esponja celulosa limpieza facial pack 2u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 700.00, 2.00, NULL, NULL),
(8, 'Mascara facial gel frio descongestivo', 4500.00, 'productos/67e223250e4a0_mascaraFacialGel.jpg', 'skincare', 'Mascara facial gel frio descongestivo - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 2250.00, 2.00, NULL, NULL),
(9, 'Mascara gel frio contorno de ojos descongestivo', 1300.00, 'productos/67e22318964cf_mascaraGelFrio.jpg', 'skincare', 'Mascara gel frio contorno de ojos descongestivo - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 650.00, 2.00, NULL, NULL),
(10, 'Mascarilla de colageno labios x10u', 4000.00, 'productos/67e223040bb3c_mascarillaColagenoLabios.jpeg', 'skincare', 'Mascarilla de colageno labios x10u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 2000.00, 2.00, NULL, NULL),
(11, 'Mascarilla facial Bling pop palta', 1500.00, 'productos/67e222ec362bc_mascarillaFacialBlingPalta.jpg', 'skincare', 'Mascarilla facial Bling pop palta - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 750.00, 2.00, NULL, NULL),
(12, 'Mascarilla facial Bling pop aloe', 1500.00, 'productos/67e222de38b65_mascarillaFacialBlingAloe.jpg', 'skincare', 'Mascarilla facial Bling pop aloe - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 750.00, 2.00, NULL, NULL),
(13, 'Mascarilla de colageno ojeras x10u', 4000.00, 'productos/67e222bd4adc0_mascarillaColagenoOjeras.jpeg', 'skincare', 'Mascarilla de colageno ojeras x10u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 2000.00, 2.00, NULL, NULL),
(14, 'Mascarilla negra peel off x10u', 2800.00, 'productos/67e2226cc7598_mascarillaNegraPeel.jpeg', 'skincare', 'Mascarilla negra peel off x10u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 1400.00, 2.00, NULL, NULL),
(15, 'Mascarillas comprimidas pack x100u', 6500.00, 'productos/67e2225e9010a_mascarillaComprimidasPackX100u.jpeg', 'skincare', 'Mascarillas comprimidas pack x100u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 3250.00, 2.00, NULL, NULL),
(16, 'Mascarillas comprimidas pack 50u', 3600.00, 'productos/67e2222c20469_mascarillaComprimidasPackX50u.jpeg', 'skincare', 'Mascarillas comprimidas pack 50u - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 1800.00, 2.00, NULL, NULL),
(17, 'Pads algodon removedor maquillaje 20pc', 1500.00, 'productos/67e2221934d87_padsAlgodonRemovedor.jpg', 'skincare', 'Pads algodon removedor maquillaje 20pc - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 750.00, 2.00, NULL, NULL),
(18, 'Piedra jade Guasha', 800.00, 'productos/67e2220a5cb54_piedraJadeGuasha.jpeg', 'skincare', 'Piedra jade Guasha - Skincare', 'Producto de Skincare\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 400.00, 2.00, NULL, NULL),
(19, 'Espejo de cartela con cepillo portatil', 1100.00, 'productos/67e221f24d290_espejoPortatil.jpg', 'accesorios', 'Espejo de cartela con cepillo portatil - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 550.00, 2.00, NULL, NULL),
(20, 'Estuche porta lentes varios colores PACK x12', 13000.00, 'productos/67e221e11927f_estuchePorta.jpg', 'accesorios', 'Estuche porta lentes varios colores PACK x12 - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 6500.00, 2.00, NULL, NULL),
(21, 'Brochecitos de mariposas pack x10u', 2500.00, 'productos/67e221d0ebbc9_brochesMaripo.jpg', 'accesorios', 'Brochecitos de mariposas pack x10u - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 1250.00, 2.00, NULL, NULL),
(22, 'Espejo de mesa', 4200.00, 'productos/67e221ba3cb1c_espejoMesa.jpg', 'accesorios', 'Espejo de mesa - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 2100.00, 2.00, NULL, NULL),
(23, 'Espejo de mesa 2', 4200.00, 'productos/67e221a3a3fe5_espejomesa2.jpg', 'accesorios', 'Espejo de mesa 2 - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 2100.00, 2.00, NULL, NULL),
(24, 'Espejo doble de mesa vintage blanco Ovalado', 10500.00, 'productos/67e2216519c95_espejoDobleMesa.jpg', 'accesorios', 'Espejo doble de mesa vintage blanco Ovalado - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 5250.00, 2.00, NULL, NULL),
(25, 'Espejo kawaii con pies de mesa celeste/rosa', 7300.00, 'productos/67e221232729c_espejoKawaiiMesa.jpg', 'accesorios', 'Espejo kawaii con pies de mesa celeste/rosa - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 3650.00, 2.00, NULL, NULL),
(26, 'Espejo kawaii de mesa grande', 6700.00, 'productos/67e22107366f9_espejoKawaiiGrande.jpg', 'accesorios', 'Espejo kawaii de mesa grande - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 3350.00, 2.00, NULL, NULL),
(27, 'Espejo LED regarcable 3 tonos de luz', 7800.00, 'productos/67e220f51a454_espejoLed.jpg', 'accesorios', 'Espejo LED regarcable 3 tonos de luz - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 3900.00, 2.00, NULL, NULL),
(28, 'Organizador flor con espejo color rosa', 9800.00, 'productos/67e220df437e5_organizadorFlor.jpg', 'accesorios', 'Organizador flor con espejo color rosa - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 4900.00, 2.00, NULL, NULL),
(29, 'Tachito basura con tapa de mesa', 4800.00, 'productos/67e220b92e442_tachitoBasura.jpg', 'accesorios', 'Tachito basura con tapa de mesa - Accesorios', 'Producto de Accesorios\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 2400.00, 2.00, NULL, NULL),
(30, 'Delineador liquido con glitter Gegebear #GX1222', 1700.00, 'productos/67e2209fd5cb4_delineadorLiquidoGlitter.jpg', 'maquillaje', 'Delineador liquido con glitter Gegebear #GX1222 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 850.00, 2.00, NULL, NULL),
(31, 'Labial gloss en pomo 8 tonos Gegebear #GX1202', 1500.00, 'productos/67e2208fd1174_labialGlossssss.jpg', 'maquillaje', 'Labial gloss en pomo 8 tonos Gegebear #GX1202 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 750.00, 2.00, NULL, NULL),
(32, 'Labial gloss solido flower Gegebear #GX1030', 1400.00, 'productos/67e22004a9547_labialGlossSolidoFlower.jpg', 'maquillaje', 'Labial gloss solido flower Gegebear #GX1030 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 700.00, 2.00, NULL, NULL),
(33, 'Labial liquido matte osito Gegebear #GX1049', 1400.00, 'productos/67e21ff43530c_labialLiquidoMatteOsito.jpg', 'maquillaje', 'Labial liquido matte osito Gegebear #GX1049 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 700.00, 2.00, NULL, NULL),
(34, 'Rubor en crema Gegebear #GX2007', 1200.00, 'productos/67e21fddd5dea_ruborCrema.jpg', 'maquillaje', 'Rubor en crema Gegebear #GX2007 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 600.00, 2.00, NULL, NULL),
(35, 'Labial gloss solido Gegebear #GX1129', 1700.00, 'productos/67e21fc97fdd8_labialGlossSolidoGege.jpg', 'maquillaje', 'Labial gloss solido Gegebear #GX1129 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 850.00, 2.00, NULL, NULL),
(36, 'Rubor jelly Gegebear #GX1159', 1300.00, 'productos/67e21fad35369_ruborJelly.jpg', 'maquillaje', 'Rubor jelly Gegebear #GX1159 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 650.00, 2.00, NULL, NULL),
(37, 'Polvo compacto Gegebear efecto matte #GX2019', 3300.00, 'productos/67e21f8ddaec4_polvoCompactoMatte.jpg', 'maquillaje', 'Polvo compacto Gegebear efecto matte #GX2019 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:01', 1650.00, 2.00, NULL, NULL),
(38, 'Rubor crema con esponjita Gegebear #GX2009', 1900.00, 'productos/67e21f7c66001_ruborCremaEsponjita.jpg', 'maquillaje', 'Rubor crema con esponjita Gegebear #GX2009 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 950.00, 2.00, NULL, NULL),
(39, 'Labial gloss solido Gegebear #GX1120', 1700.00, 'productos/67e21f6b2b118_labialGlossSolido.jpg', 'maquillaje', 'Labial gloss solido Gegebear #GX1120 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 850.00, 2.00, NULL, NULL),
(40, 'Labial matte Gegebear #GX1107', 1500.00, 'productos/67e21f55e4cf0_labialMatteGege.jpg', 'maquillaje', 'Labial matte Gegebear #GX1107 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 750.00, 2.00, NULL, NULL),
(41, 'Cajita x3 labiales gloss solidos Gegebear #GX1098', 4500.00, 'productos/67e21f1a35559_cajitax3LabialSolidos.jpg', 'maquillaje', 'Cajita x3 labiales gloss solidos Gegebear #GX1098 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(42, 'Labial liquido matte Gegebear #GX1079', 1500.00, 'productos/67e21f0abaa87_labialLiquidoMatteGX1079.jpg', 'maquillaje', 'Labial liquido matte Gegebear #GX1079 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 750.00, 2.00, NULL, NULL),
(43, 'Labial gloss osito Gegebear #GX1071', 1600.00, 'productos/67e21eefb7f10_labialGlossOsito.jpg', 'maquillaje', 'Labial gloss osito Gegebear #GX1071 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 800.00, 2.00, NULL, NULL),
(44, 'Labial gloss Gegebear #GX1057', 1400.00, 'productos/67e21e88cba5d_labialGlossGege.jpg', 'maquillaje', 'Labial gloss Gegebear #GX1057 - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 700.00, 2.00, NULL, NULL),
(45, 'Lipstick osito Gegebear varios colores', 2200.00, 'productos/67e21e22542f0_lipstickOsito.jpg', 'maquillaje', 'Lipstick osito Gegebear varios colores - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 1100.00, 2.00, NULL, NULL),
(46, 'Labial gloss jirafa Gegebear', 2200.00, 'productos/67e21e121cecb_labialGlossJirafa.jpg', 'maquillaje', 'Labial gloss jirafa Gegebear - Maquillaje', 'Producto de Maquillaje\r\nProducto de calidad', '-', 5.0, 0, '2025-03-25 00:26:02', 1100.00, 2.00, NULL, NULL),
(47, 'Cajita paleta de contorno 4 tonos en 1 Art value #A09', 2900.00, 'productos/67e21df3390d4_cajitaPaletaValueA09.jpg', 'maquillaje', 'Cajita paleta de contorno 4 tonos en 1 Art value #A09 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1450.00, 2.00, NULL, NULL),
(48, 'Cajita de sombras 4 colores en 1 gatito Art value #A24', 2900.00, 'productos/67e21de21f870_cajitaSombras4gatitoValueA24.jpg', 'maquillaje', 'Cajita de sombras 4 colores en 1 gatito Art value #A24 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1450.00, 2.00, NULL, NULL),
(49, 'Labial en barra matte Art value #A76', 2000.00, 'productos/67e21dd046032_LabialEnBarraMatteArtValue A76.jpg', 'maquillaje', 'Labial en barra matte Art value #A76 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1000.00, 2.00, NULL, NULL),
(50, 'Polvo compacto piano Art value waterproof #A02', 2600.00, 'productos/67e21dc0c96ec_polvoCompactoPianoA02.jpg', 'maquillaje', 'Polvo compacto piano Art value waterproof #A02 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1300.00, 2.00, NULL, NULL),
(51, 'Polvo compacto leoncito Art value #A13', 2600.00, 'productos/67e21da26d9dd_polvoCompactoLeoncitoA13.jpg', 'maquillaje', 'Polvo compacto leoncito Art value #A13 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1300.00, 2.00, NULL, NULL),
(52, 'Rubor huevito Art value #A2412', 2100.00, 'productos/67e21d94bd247_ruborHuevitoA2412.jpg', 'maquillaje', 'Rubor huevito Art value #A2412 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1050.00, 2.00, NULL, NULL),
(53, 'Cajita de sombra 4 colores en 1 Art value #A08', 2900.00, 'productos/67e21d841db9c_cajitaSombrasColoresA08.jpg', 'maquillaje', 'Cajita de sombra 4 colores en 1 Art value #A08 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1450.00, 2.00, NULL, NULL),
(54, 'Rubor con espejito floral Art value #A59', 2100.00, 'productos/67e21d73baf2c_ruborEspejitoA59.jpg', 'maquillaje', 'Rubor con espejito floral Art value #A59 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1050.00, 2.00, NULL, NULL),
(55, 'Polvo compacto osito Art value #A44', 2900.00, 'productos/67e21d63cca2f_polvoCompOsitoA44.jpg', 'maquillaje', 'Polvo compacto osito Art value #A44 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1450.00, 2.00, NULL, NULL),
(56, 'Labial matte panda llavero Art value #A54', 2000.00, 'productos/67e21d553354b_labialMAttePandaA54.jpg', 'maquillaje', 'Labial matte panda llavero Art value #A54 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1000.00, 2.00, NULL, NULL),
(57, 'Paleta de sombras 4 tonos Art value #A16', 3000.00, 'productos/67e21d47290ba_paletaSombrasA16.jpg', 'maquillaje', 'Paleta de sombras 4 tonos Art value #A16 - Maquillaje de la marca VALUE', 'Producto de Maquillaje\r\nMarca: VALUE', '-', 5.0, 0, '2025-03-25 00:26:02', 1500.00, 2.00, NULL, NULL),
(58, 'Labial gloss en barra 4en1 desmontable Cappuvini #CP154', 1500.00, 'productos/67e21d05f1fcc_labialGlossDesmontableCP154.jpg', 'maquillaje', 'Labial gloss en barra 4en1 desmontable Cappuvini #CP154 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 750.00, 2.00, NULL, NULL),
(59, 'Lapiz labial nude Cappuvini varios tonos #CP140', 1400.00, 'productos/67e21cf679853_lapizLabialNudeCP140.jpg', 'maquillaje', 'Lapiz labial nude Cappuvini varios tonos #CP140 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 700.00, 2.00, NULL, NULL),
(60, 'Labial gloss solido mini Cappuvini #CP251', 1100.00, 'productos/67e21ce37967a_labialGlossSolidoCP251.jpg', 'maquillaje', 'Labial gloss solido mini Cappuvini #CP251 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 550.00, 2.00, NULL, NULL),
(61, 'Labial liquido gloss Cappuvini #A64', 1500.00, 'productos/67e21cd4d9b4f_labialLiqGloss.jpg', 'maquillaje', 'Labial liquido gloss Cappuvini #A64 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 750.00, 2.00, NULL, NULL),
(62, 'Labial gloss dino varios tonos Cappuvini #A01', 1400.00, 'productos/67e21cb2c668b_labialGlossDinoA01.jpg', 'maquillaje', 'Labial gloss dino varios tonos Cappuvini #A01 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 700.00, 2.00, NULL, NULL),
(63, 'Labial gloss brillo Cappuvini #CP24', 1300.00, 'productos/67e21ca3740ba_labialGlossBrilloCP24.jpg', 'maquillaje', 'Labial gloss brillo Cappuvini #CP24 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 650.00, 2.00, NULL, NULL),
(64, 'Balsamo de labios miel Cappuvini #CP05', 1400.00, 'productos/67e21c9566b20_balsamoLabiosMielCP05.jpg', 'maquillaje', 'Balsamo de labios miel Cappuvini #CP05 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 700.00, 2.00, NULL, NULL),
(65, 'Kit 3 labiales gloss Cappuvini #CP122', 3200.00, 'productos/67e21c85df719_kitLabialesGlossCP122.jpg', 'maquillaje', 'Kit 3 labiales gloss Cappuvini #CP122 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 1600.00, 2.00, NULL, NULL),
(66, 'Polvo traslucido Cappuvini #CP99', 2200.00, 'productos/67e21c758853b_polvoTraslucidoCP99.jpg', 'maquillaje', 'Polvo traslucido Cappuvini #CP99 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 1100.00, 2.00, NULL, NULL),
(67, 'Kit 3 labiales doble gloss+matte Cappuvini', 3500.00, 'productos/67e21c68237b4_kitLabialesDoble.jpg', 'maquillaje', 'Kit 3 labiales doble gloss+matte Cappuvini - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 1750.00, 2.00, NULL, NULL),
(68, 'Labial matte osito Cappuvini #CP89', 1100.00, 'productos/67e21c47b5369_labialMatteOsitoCP89.jpg', 'maquillaje', 'Labial matte osito Cappuvini #CP89 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 550.00, 2.00, NULL, NULL),
(69, 'Labial gloss forma del vasito Cappuvini #CP244', 1300.00, 'productos/67e21c0bbac9e_labialGlossFormaCP244.jpg', 'maquillaje', 'Labial gloss forma del vasito Cappuvini #CP244 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 650.00, 2.00, NULL, NULL),
(70, 'Corrector liquido Cappuvini #A60', 1400.00, 'productos/67e21b69c0fbc_correctorLiqA60.jpg', 'maquillaje', 'Corrector liquido Cappuvini #A60 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 700.00, 2.00, NULL, NULL),
(71, 'Pestañas autoadhesivas Cappuvini efecto manga #J01-02', 2600.00, 'productos/67e21b57f371f_pestañasAutoadhesivasJ0102.jpg', 'maquillaje', 'Pestañas autoadhesivas Cappuvini efecto manga #J01-02 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 1300.00, 2.00, NULL, NULL),
(72, 'Pestañas autoadhesivas Cappuvini #J01-03', 2600.00, 'productos/67e21b487f0c2_pestañasAutoJ0103.jpg', 'maquillaje', 'Pestañas autoadhesivas Cappuvini #J01-03 - Maquillaje de la marca Cappuvini', 'Producto de Maquillaje\r\nMarca: Cappuvini', '-', 5.0, 0, '2025-03-25 00:26:02', 1300.00, 2.00, NULL, NULL),
(73, 'Magic lip gloss Jojo Diary', 1700.00, 'productos/67e21aa77338f_magicLipGlossJojo.jpg', 'maquillaje', 'Magic lip gloss Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 850.00, 2.00, NULL, NULL),
(76, 'Base cussion waterproof Jojo Diary 2 tonos', 3900.00, 'productos/67e21a793bd46_baseCussionWaterproofJojo2.jpg', 'maquillaje', 'Base cussion waterproof Jojo Diary 2 tonos - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1950.00, 2.00, NULL, NULL),
(77, 'Base cussion efecto matte Jojo Diary', 4500.00, 'productos/67e21a57adf39_baseCussionMatteJojo.jpg', 'maquillaje', 'Base cussion efecto matte Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(78, 'Base cussion con esponja osito Jojo Diary', 4500.00, 'productos/67e21a2ad661f_baseCussionEspoOsitoJojo.jpg', 'maquillaje', 'Base cussion con esponja osito Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(79, 'Cajita de papel absorbente de grasa uso facial Jojo Diary', 1800.00, 'productos/67e21a1917d0b_cajitaPapelAbsJojo.jpg', 'maquillaje', 'Cajita de papel absorbente de grasa uso facial Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 900.00, 2.00, NULL, NULL),
(80, 'Base cussion 2 tonos efecto matte waterproof Jojo Diary', 4500.00, 'productos/67e21a072169c_baseCussionMatteWaterproofJojo.jpg', 'maquillaje', 'Base cussion 2 tonos efecto matte waterproof Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(81, 'Base cremosa en pote Jojo Diary', 5200.00, 'productos/67e21956c4e78_baseCremosaPoteJojo.png', 'maquillaje', 'Base cremosa en pote Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2600.00, 2.00, NULL, NULL),
(82, 'Rubor en cussion Jojo Diary', 2300.00, 'productos/67e218e813d6b_ruborCussionJojo.jpg', 'maquillaje', 'Rubor en cussion Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1150.00, 2.00, NULL, NULL),
(83, 'Base cussion + paleta corrector Jojo Diary', 5500.00, 'productos/67e218ce0eef4_basesitaaa.jpg', 'maquillaje', 'Base cussion + paleta corrector Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2750.00, 2.00, NULL, NULL),
(85, 'Polvo compacto matte Jojo Diary', 2800.00, 'productos/67e217c5db669_polvitoh2.jpg', 'maquillaje', 'Polvo compacto matte Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1400.00, 2.00, NULL, NULL),
(86, 'Polvo compacto sellante 3 tonos Jojo Diary', 3300.00, 'productos/67e2178191d0a_polviitooh.jpg', 'maquillaje', 'Polvo compacto sellante 3 tonos Jojo Diary - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1650.00, 2.00, NULL, NULL),
(87, 'Lips gloss solido en pomo Novo #6313', 1900.00, 'productos/67e2170cda0d7_lipsGlossNovo6313.jpg', 'maquillaje', 'Lips gloss solido en pomo Novo #6313 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 950.00, 2.00, NULL, NULL),
(88, 'Balsamo hidratante de labios con fragancia Novo #6286', 1600.00, 'productos/67e216c0922c7_balsamoHidratanteNovo6286.jpg', 'maquillaje', 'Balsamo hidratante de labios con fragancia Novo #6286 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 800.00, 2.00, NULL, NULL),
(89, 'Sombra glitter brillante con aplicador Novo #6344', 1700.00, 'productos/67e216abbf1bd_sombraGlitterNovo6344.jpg', 'maquillaje', 'Sombra glitter brillante con aplicador Novo #6344 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 850.00, 2.00, NULL, NULL),
(90, 'Serum facial de niacinamida 100ml Novo #6296', 4700.00, 'productos/67e21699891eb_serumFacialNiacinamidaNovo6296.jpg', 'maquillaje', 'Serum facial de niacinamida 100ml Novo #6296 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2350.00, 2.00, NULL, NULL),
(91, 'Paleta contorno+iluminador Novo #6414', 2900.00, 'productos/67e2168424b3b_paletaContornoNovo6414.jpg', 'maquillaje', 'Paleta contorno+iluminador Novo #6414 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1450.00, 2.00, NULL, NULL),
(92, 'Primer en crema con protector solar Novo #6285', 3200.00, 'productos/67e216410d36c_pimerCremaProtNovo6285.jpg', 'maquillaje', 'Primer en crema con protector solar Novo #6285 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1600.00, 2.00, NULL, NULL),
(93, 'Base fluida pomo ideal para piel seca Novo #6315', 4000.00, 'productos/67e2162de6c72_baseFkuidaNovo6315.jpg', 'maquillaje', 'Base fluida pomo ideal para piel seca Novo #6315 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2000.00, 2.00, NULL, NULL),
(94, 'Eye essence serum para ojeras anti-arruga Novo #6302', 3000.00, 'productos/67e21613b3a61_eyeEssenceNovo6302.jpg', 'maquillaje', 'Eye essence serum para ojeras anti-arruga Novo #6302 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1500.00, 2.00, NULL, NULL),
(95, 'Delineador en fibra punta ultra fina waterproof Novo #6050', 2000.00, 'productos/67e215e2636fc_delineadorFibraPuntaNovo6050.jpg', 'maquillaje', 'Delineador en fibra punta ultra fina waterproof Novo #6050 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1000.00, 2.00, NULL, NULL),
(96, 'Base cussion resistente tapa manchas Novo #5589', 4500.00, 'productos/67e215c153e27_baseCussionResistNovo5589.jpg', 'maquillaje', 'Base cussion resistente tapa manchas Novo #5589 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(97, 'Polvo traslucido Novo #6373', 3200.00, 'productos/67e215a78e543_polvoTraslucidoNovo6373.jpg', 'maquillaje', 'Polvo traslucido Novo #6373 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1600.00, 2.00, NULL, NULL),
(98, 'Flower lips gloss Novo varios colores #5794', 1900.00, 'productos/67e2159381d86_floweLipsNovo5794.jpg', 'maquillaje', 'Flower lips gloss Novo varios colores #5794 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 950.00, 2.00, NULL, NULL),
(99, 'Corrector crema en pomo 3 colores Novo #6272', 2700.00, 'productos/67e2158159293_correctorCremaNovo6272.jpg', 'maquillaje', 'Corrector crema en pomo 3 colores Novo #6272 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1350.00, 2.00, NULL, NULL),
(100, 'Iluminador ultra brillante Novo #6319', 2700.00, 'productos/67e21570658f5_iluminadorUltraNovo6319.jpg', 'maquillaje', 'Iluminador ultra brillante Novo #6319 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1350.00, 2.00, NULL, NULL),
(101, 'Base cussion Novo nutritivo #6261', 4800.00, 'productos/67e21560890f1_baseCussionNovo6261.jpg', 'maquillaje', 'Base cussion Novo nutritivo #6261 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2400.00, 2.00, NULL, NULL),
(102, 'Hidratante de labios en barra Novo #6338', 1600.00, 'productos/67e2155137c09_hidratanteLabiosNovo6338.jpg', 'maquillaje', 'Hidratante de labios en barra Novo #6338 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 800.00, 2.00, NULL, NULL),
(103, 'Desodorante roll on con fragancia Novo #6168', 2400.00, 'productos/67e215408d95d_desodoranteNovo6168.jpg', 'maquillaje', 'Desodorante roll on con fragancia Novo #6168 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1200.00, 2.00, NULL, NULL),
(104, 'Polvo compacto sellante Novo #5679', 5000.00, 'productos/67e215304f607_polvoCompactoNovo5679.jpg', 'maquillaje', 'Polvo compacto sellante Novo #5679 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2500.00, 2.00, NULL, NULL),
(105, 'Polvo traslucido unicorn Novo #5386', 2500.00, 'productos/67e2147237091_polvoTraslucidoUnicornNovo5386.jpg', 'maquillaje', 'Polvo traslucido unicorn Novo #5386 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1250.00, 2.00, NULL, NULL),
(106, 'Lip stick matte Novo intransferible #5762', 2200.00, 'productos/67e2140c3b76e_lipStickMatte5762.jpg', 'maquillaje', 'Lip stick matte Novo intransferible #5762 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1100.00, 2.00, NULL, NULL),
(107, 'Base cremosa en pomo Novo #6245', 4500.00, 'productos/67e213ea8e82a_baseCremosaNovo6245.jpg', 'maquillaje', 'Base cremosa en pomo Novo #6245 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(108, 'Desodorante solido con fragancias Novo #6097', 3000.00, 'productos/67e213d2c8841_desodoranteSolidoNovo6097.jpg', 'maquillaje', 'Desodorante solido con fragancias Novo #6097 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1500.00, 2.00, NULL, NULL),
(109, 'Base liquida velvet Novo #6148', 5000.00, 'productos/67e213c410c80_baseLiqVelvetNovo6148.jpg', 'maquillaje', 'Base liquida velvet Novo #6148 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2500.00, 2.00, NULL, NULL),
(110, 'Paleta corrector + iluminador Novo #6196', 3000.00, 'productos/67e213a1f31ec_paletaCorrectorIluNovo6196.jpg', 'maquillaje', 'Paleta corrector + iluminador Novo #6196 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1500.00, 2.00, NULL, NULL),
(111, 'Lapiz doble iluminador + contorno Novo #5494', 3000.00, 'productos/67e2137d64dad_lapizDobleNovo5494.jpg', 'maquillaje', 'Lapiz doble iluminador + contorno Novo #5494 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1500.00, 2.00, NULL, NULL),
(112, 'Paleta de sombra 9 tonos Novo #5816', 4800.00, 'productos/67e213656490c_paletaSOmbraNovo5816.jpg', 'maquillaje', 'Paleta de sombra 9 tonos Novo #5816 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2400.00, 2.00, NULL, NULL),
(113, 'Rubor degrade paleta Novo #6103', 3100.00, 'productos/67e2134f38372_ruborDegradeNovo6103.jpg', 'maquillaje', 'Rubor degrade paleta Novo #6103 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1550.00, 2.00, NULL, NULL),
(114, 'Base crema en pomo Novo color natural #6048', 5000.00, 'productos/67e2126ac8af3_baseCremaNovo6048.jpg', 'maquillaje', 'Base crema en pomo Novo color natural #6048 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2500.00, 2.00, NULL, NULL),
(115, 'Delineador ultrafino waterproof Novo #5950', 2200.00, 'productos/67e2125fd7231_delineadorUltrafinoNovo5950.jpg', 'maquillaje', 'Delineador ultrafino waterproof Novo #5950 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1100.00, 2.00, NULL, NULL),
(116, 'Paleta de sombras 4 tonos Novo #5330', 2900.00, 'productos/67e2124287298_paletaSombrasNovo5330.jpg', 'maquillaje', 'Paleta de sombras 4 tonos Novo #5330 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1450.00, 2.00, NULL, NULL),
(117, 'Delineador liquido ultra fino waterproof Novo #5895', 1600.00, 'productos/67e2123102b9b_delineadorLiqNovo5895.jpg', 'maquillaje', 'Delineador liquido ultra fino waterproof Novo #5895 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 800.00, 2.00, NULL, NULL),
(118, 'Paleta de sombras Novo #5462', 4500.00, 'productos/67e21222df186_paletaSombrasNovo5462.jpg', 'maquillaje', 'Paleta de sombras Novo #5462 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 2250.00, 2.00, NULL, NULL),
(119, 'Rimmel cepillo fino levanta pestañas Novo #6067', 2200.00, 'productos/67e21206c1bd1_rimmelCepilloNovo6067.jpg', 'maquillaje', 'Rimmel cepillo fino levanta pestañas Novo #6067 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1100.00, 2.00, NULL, NULL),
(120, 'Rubor moño con esponjita Novo #6210', 2600.00, 'productos/67e211f7616d7_ruborMoñoNovo6210.jpg', 'maquillaje', 'Rubor moño con esponjita Novo #6210 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1300.00, 2.00, NULL, NULL),
(121, 'Labial en barra matte larga duracion Novo #6208', 1900.00, 'productos/67e211e819479_labialBarraNovo6208.jpg', 'maquillaje', 'Labial en barra matte larga duracion Novo #6208 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 950.00, 2.00, NULL, NULL),
(122, 'Paleta corrector con cepillo Novo #6141', 2400.00, 'productos/67e211b3645ff_paletaCorrectorNovo6141.jpg', 'maquillaje', 'Paleta corrector con cepillo Novo #6141 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1200.00, 2.00, NULL, NULL),
(123, 'Rimmel ultra fino waterproof Novo #6173', 1500.00, 'productos/67e2117a86258_rimmelUltraFinoNovo6173.jpg', 'maquillaje', 'Rimmel ultra fino waterproof Novo #6173 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 750.00, 2.00, NULL, NULL),
(124, 'Labial liquido matte Novo #5411', 2000.00, 'productos/67e2111f89d42_labialLiqNovo5411.jpg', 'maquillaje', 'Labial liquido matte Novo #5411 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1000.00, 2.00, NULL, NULL),
(125, 'Labial liquido matte velvet Novo #8099', 1900.00, 'productos/67e211099736b_labialLiqNovo8099.jpg', 'maquillaje', 'Labial liquido matte velvet Novo #8099 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 950.00, 2.00, NULL, NULL),
(126, 'Crema de manos Novo #6049', 3300.00, 'productos/67e210f665ce8_cremaMAnosNOvo6049.jpg', 'maquillaje', 'Crema de manos Novo #6049 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1650.00, 2.00, NULL, NULL),
(127, 'Rubor cremoso en pote Novo #5885', 2500.00, 'productos/67e210e892bdf_ruborCremosoNovo5885.jpg', 'maquillaje', 'Rubor cremoso en pote Novo #5885 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1250.00, 2.00, NULL, NULL),
(128, 'Balsamo labial hidratante nocturna Novo #6069', 2300.00, 'productos/67e210d7b52e9_balsamoLabialNovo6069.jpg', 'maquillaje', 'Balsamo labial hidratante nocturna Novo #6069 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1150.00, 2.00, NULL, NULL),
(129, 'Paleta iluminador ultrabrillante Novo #5833', 3700.00, 'productos/67e210679da4f_paletaIlumiNovo5833.jpg', 'maquillaje', 'Paleta iluminador ultrabrillante Novo #5833 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1850.00, 2.00, NULL, NULL),
(130, 'Labial en barra matte waterproof Novo #5979', 2300.00, 'productos/67e20e59dab97_labialBArraMatteNovo5979.jpg', 'maquillaje', 'Labial en barra matte waterproof Novo #5979 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1150.00, 2.00, NULL, NULL),
(131, 'Rubor en paleta Novo #5916', 2100.00, 'productos/67e20e416cd1f_RuborPaletaNovo5916.jpg', 'maquillaje', 'Rubor en paleta Novo #5916 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 1050.00, 2.00, NULL, NULL),
(132, 'Lip gloss matte Novo #5882', 1900.00, 'productos/67e20de362ac3_lipGlossMatteNovo5882.jpg', 'maquillaje', 'Lip gloss matte Novo #5882 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:02', 950.00, 2.00, NULL, NULL),
(133, 'Sombra individual Novo #5988', 1700.00, 'productos/67e20dae7b699_sombraIndividualNovo5988.jpg', 'maquillaje', 'Sombra individual Novo #5988 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 850.00, 2.00, NULL, NULL),
(134, 'Novo labial gloss juice lips #6095', 2000.00, 'productos/67e20d44652af_labialGlossJuiceNovo6095.jpg', 'maquillaje', 'Novo labial gloss juice lips #6095 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 1000.00, 2.00, NULL, NULL),
(135, 'Novo balsamo labial hidratante frutal #5414', 2500.00, 'productos/67e20d19e4690_balsamoLabialNovo5414.jpg', 'maquillaje', 'Novo balsamo labial hidratante frutal #5414 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 1250.00, 2.00, NULL, NULL),
(136, 'Novo base cussion + polvo compacto 2en1 #6090', 7000.00, 'productos/67e20d0b7c48d_baseCussionPolvoNovo6090.jpg', 'maquillaje', 'Novo base cussion + polvo compacto 2en1 #6090 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 3500.00, 2.00, NULL, NULL),
(137, 'Novo mascara de pestañas doble cepillos #6071', 2900.00, 'productos/67e20c5659e1b_mascaraPestDobleNovo6071.jpg', 'maquillaje', 'Novo mascara de pestañas doble cepillos #6071 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 1450.00, 2.00, NULL, NULL),
(138, 'Novo rubor con esponjita #6098', 2300.00, 'productos/67e20c0c199ea_ruborEspónjNovo6098.jpg', 'maquillaje', 'Novo rubor con esponjita #6098 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 1150.00, 2.00, NULL, NULL),
(139, 'Novo paleta de sombra 4 colores #5815', 3400.00, 'productos/67e20b6e0de04_paletaSombraColoNovo5815.jpg', 'maquillaje', 'Novo paleta de sombra 4 colores #5815 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 1700.00, 2.00, NULL, NULL),
(140, 'Novo polvo compacto #5935', 6800.00, 'productos/67e20b26efff9_novoPolvoCompactNovo5935.jpg', 'maquillaje', 'Novo polvo compacto #5935 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 3400.00, 2.00, NULL, NULL),
(141, 'Novo Paleta maquillaje + rubor #6089', 8400.00, 'productos/67e20bc2c8787_novoPaletaMAqNovo6089.jpg', 'maquillaje', 'Novo Paleta maquillaje + rubor #6089 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 4200.00, 2.00, NULL, NULL),
(142, 'Novo lips stick Gloss #5989', 6000.00, 'productos/67e20b99a15c5_lipsStickNovo5989.jpg', 'maquillaje', 'Novo lips stick Gloss #5989 - Maquillaje de la marca JOJO DIARY', 'Producto de Maquillaje\r\nMarca: JOJO DIARY', '-', 5.0, 0, '2025-03-25 00:26:03', 3000.00, 2.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `tipo` enum('porcentaje','monto_fijo','especial') NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activa` tinyint(1) DEFAULT 1,
  `categoria` varchar(50) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `destacada` tinyint(1) DEFAULT 0,
  `margen_minimo` decimal(4,2) DEFAULT 1.30,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones_productos`
--

CREATE TABLE `promociones_productos` (
  `id` int(11) NOT NULL,
  `promocion_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suscriptores`
--

CREATE TABLE `suscriptores` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `fecha_suscripcion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `usuario`, `password`, `email`, `fecha_registro`) VALUES
(1, 'Administrador', 'administradora', 'bear', 'admin@bearshop.com', '2025-03-24 18:03:58');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indices de la tabla `margenes_categoria`
--
ALTER TABLE `margenes_categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categoria` (`categoria`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_id` (`pedido_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `promociones_productos`
--
ALTER TABLE `promociones_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promocion_id` (`promocion_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `suscriptores`
--
ALTER TABLE `suscriptores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `margenes_categoria`
--
ALTER TABLE `margenes_categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `promociones_productos`
--
ALTER TABLE `promociones_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `suscriptores`
--
ALTER TABLE `suscriptores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`),
  ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `promociones_productos`
--
ALTER TABLE `promociones_productos`
  ADD CONSTRAINT `promociones_productos_ibfk_1` FOREIGN KEY (`promocion_id`) REFERENCES `promociones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promociones_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
