// Este script genera un archivo CSV que puedes abrir con Excel
// para importar tus productos a la base de datos

const fs = require('fs');

// Función para limpiar el precio y convertirlo a número
function limpiarPrecio(precioStr) {
  return parseFloat(precioStr.replace('$', '').replace('.', '').replace(',', '.').trim());
}

// Función para generar un nombre de archivo de imagen basado en el nombre del producto
function generarNombreImagen(nombre) {
  return 'productos/' + nombre.toLowerCase().replace(/[^\w\s]/gi, '').replace(/\s+/g, '-').substring(0, 30) + '.jpg';
}

// Datos de productos desde el texto proporcionado
const texto = `
Tipo: Skincare


Caja pads algodon suave sin pelusa 150pc #20082 $ 6.200,00

Caja x10u parches removedor puntos negros Ymxercos $ 1.800,00

Caja parche para acne 48pc Kormesic $ 2.700,00

Kit ice globes 2pc City girls rosa/celeste $ 10.000,00 

Esponja celulosa City Girls pack x2u $ 1.400,00
 
Esponja celulosa limpieza facial pack 2u $ 1.400,00

Mascara facial gel frio descongestivo $ 4.500,00

Mascara gel frio contorno de ojos descongestivo $ 1.300,00 

Mascarilla de colageno labios x10u $ 4.000,00 

Mascarilla facial Bling pop palta $ 1.500,00 

Mascarilla facial Bling pop aloe $ 1.500,00

Mascarilla de colageno ojeras x10u $ 4.000,00

Mascarilla negra peel off x10u $ 2.800,00

Mascarillas comprimidas pack x100u $ 6.500,00

Mascarillas comprimidas pack 50u $ 3.600,00

Pads algodon removedor maquillaje 20pc $ 1.500,00

Piedra jade Guasha $ 800,00 

Tipo:Accesorios

Espejo de cartela con cepillo portatil $ 1.100,00

Estuche porta lentes varios colores PACK x12 $ 13.000,00

Brochecitos de mariposas pack x10u $ 2.500,00

Espejo de mesa $ 4.200,00

Espejo de mesa 2 $ 4.200,00

Espejo doble de mesa vintage blanco Ovalado $ 10.500,00

Espejo kawaii con pies de mesa celeste/rosa $ 7.300,00

Espejo kawaii de mesa grande $ 6.700,00

Espejo LED regarcable 3 tonos de luz $ 7.800,00

Organizador flor con espejo color rosa $ 9.800,00

Tachito basura con tapa de mesa $ 4.800,00

Tipo: Maquillaje

Delineador liquido con glitter Gegebear #GX1222 $ 1.700,00

Labial gloss en pomo 8 tonos Gegebear #GX1202 $ 1.500,00

Labial gloss solido flower Gegebear #GX1030 $ 1.400,00

Labial liquido matte osito Gegebear #GX1049 $ 1.400,00

Rubor en crema Gegebear #GX2007 $ 1.200,00

Labial gloss solido Gegebear #GX1129 $ 1.700,00

Rubor jelly Gegebear #GX1159 $ 1.300,00

Polvo compacto Gegebear efecto matte #GX2019 $ 3.300,00

Rubor crema con esponjita Gegebear #GX2009 $ 1.900,00

Labial gloss solido Gegebear #GX1120 $ 1.700,00

Labial matte Gegebear #GX1107 $ 1.500,00

Cajita x3 labiales gloss solidos Gegebear #GX1098 $ 4.500,00

Labial liquido matte Gegebear #GX1079 $ 1.500,00

Labial gloss osito Gegebear #GX1071 $ 1.600,00

Labial gloss Gegebear #GX1057 $ 1.400,00

Lipstick osito Gegebear varios colores $ 2.200,00

Labial gloss jirafa Gegebear $ 2.200,00

marca: VALUE
Cajita paleta de contorno 4 tonos en 1 Art value #A09 $ 2.900,00

Cajita de sombras 4 colores en 1 gatito Art value #A24 $ 2.900,00

Labial en barra matte Art value #A76 $ 2.000,00

Polvo compacto piano Art value waterproof #A02 $ 2.600,00

Polvo compacto leoncito Art value #A13 $ 2.600,00

Rubor huevito Art value #A2412 $ 2.100,00

Cajita de sombra 4 colores en 1 Art value #A08 $ 2.900,00

Rubor con espejito floral Art value #A59 $ 2.100,00

Polvo compacto osito Art value #A44 $ 2.900,00

Labial matte panda llavero Art value #A54 $ 2.000,00

Paleta de sombras 4 tonos Art value #A16 $ 3.000,00

Marca: Cappuvini 

Labial gloss en barra 4en1 desmontable Cappuvini #CP154 $ 1.500,00

Lapiz labial nude Cappuvini varios tonos #CP140 $ 1.400,00

Labial gloss solido mini Cappuvini #CP251 $ 1.100,00

Labial liquido gloss Cappuvini #A64 $ 1.500,00

Labial gloss dino varios tonos Cappuvini #A01 $ 1.400,00

Labial gloss brillo Cappuvini #CP24 $ 1.300,00 

Balsamo de labios miel Cappuvini #CP05 $ 1.400,00

Kit 3 labiales gloss Cappuvini #CP122 $ 3.200,00

Polvo traslucido Cappuvini #CP99 $ 2.200,00

Kit 3 labiales doble gloss+matte Cappuvini $ 3.500,00

Labial matte osito Cappuvini #CP89 $ 1.100,00

Labial gloss forma del vasito Cappuvini #CP244 $ 1.300,00

Corrector liquido Cappuvini #A60 $ 1.400,00

Pestañas autoadhesivas Cappuvini efecto manga #J01-02 $ 2.600,00


Pestañas autoadhesivas Cappuvini #J01-03 $ 2.600,00

Marca: JOJO DIARY

Magic lip gloss Jojo Diary $ 1.700,00

Base cussion waterproof efecto matte Jojo Diary 2 tonos $ 3.900,00

Base cussion waterproof Jojo Diary 2 tonos $ 3.900,00

Base cussion waterproof Jojo Diary 2 tonos $ 3.900,00

Base cussion efecto matte Jojo Diary $ 4.500,00

Base cussion con esponja osito Jojo Diary $ 4.500,00

Cajita de papel absorbente de grasa uso facial Jojo Diary $ 1.800,00

Base cussion 2 tonos efecto matte waterproof Jojo Diary $ 4.500,00

Base cremosa en pote Jojo Diary $ 5.200,00

Rubor en cussion Jojo Diary $ 2.300,00

Base cussion + paleta corrector Jojo Diary $ 5.500,00

Polvo compacto matte Jojo Diary $ 2.800,00

Polvo compacto matte Jojo Diary $ 2.800,00

Polvo compacto sellante 3 tonos Jojo Diary $ 3.300,00

Marca : NOVO

Lips gloss solido en pomo Novo #6313 $ 1.900,00

Balsamo hidratante de labios con fragancia Novo #6286 $ 1.600,00

Sombra glitter brillante con aplicador Novo #6344 $ 1.700,00

Serum facial de niacinamida 100ml Novo #6296 $ 4.700,00

Paleta contorno+iluminador Novo #6414 $ 2.900,00 

Primer en crema con protector solar Novo #6285 $ 3.200,00

Base fluida pomo ideal para piel seca Novo #6315 $ 4.000,00

Eye essence serum para ojeras anti-arruga Novo #6302 $ 3.000,00

Delineador en fibra punta ultra fina waterproof Novo #6050 $ 2.000,00

Base cussion resistente tapa manchas Novo #5589 $ 4.500,00

Polvo traslucido Novo #6373 $ 3.200,00

Flower lips gloss Novo varios colores #5794 $ 1.900,00

Corrector crema en pomo 3 colores Novo #6272 $ 2.700,00

Iluminador ultra brillante Novo #6319 $ 2.700,00

Base cussion Novo nutritivo #6261 $ 4.800,00

Hidratante de labios en barra Novo #6338 $ 1.600,00

Desodorante roll on con fragancia Novo #6168 $ 2.400,00

Polvo compacto sellante Novo #5679 $ 5.000,00

Polvo traslucido unicorn Novo #5386  $ 2.500,00

Lip stick matte Novo intransferible #5762 $ 2.200,00

Base cremosa en pomo Novo #6245 $ 4.500,00

Desodorante solido con fragancias Novo #6097 $ 3.000,00

Base liquida velvet Novo #6148 $ 5.000,00

Paleta corrector + iluminador Novo #6196 $ 3.000,00

Lapiz doble iluminador + contorno Novo #5494 $ 3.000,00

Paleta de sombra 9 tonos Novo #5816 $ 4.800,00

Rubor degrade paleta Novo #6103 $ 3.100,00

Base crema en pomo Novo color natural #6048 $ 5.000,00

Delineador ultrafino waterproof Novo #5950 $ 2.200,00

Paleta de sombras 4 tonos Novo #5330 $ 2.900,00

Delineador liquido ultra fino waterproof Novo #5895 $ 1.600,00

Paleta de sombras animalitos Novo #5419 $ 5.000,00 (agotado)

Paleta de sombras Novo #5462 $ 4.500,00

Rimmel cepillo fino levanta pestañas Novo #6067 $ 2.200,00

Rubor moño con esponjita Novo #6210 $ 2.600,00

Labial en barra matte larga duracion Novo #6208 $ 1.900,00

Paleta corrector con cepillo Novo #6141 $ 2.400,00

Rimmel ultra fino waterproof Novo #6173 $ 1.500,00

Labial liquido matte Novo #5411 $ 2.000,00

Labial liquido matte velvet Novo #8099 $ 1.900,00

Crema de manos Novo #6049 $ 3.300,00

Rubor cremoso en pote Novo #5885  $ 2.500,00

Balsamo labial hidratante nocturna Novo #6069 $ 2.300,00

Paleta iluminador ultrabrillante Novo #5833 $ 3.700,00

Labial en barra matte waterproof Novo #5979 $ 2.300,00

Rubor en paleta Novo #5916 $ 2.100,00

Lip gloss matte Novo #5882 $ 1.900,00

Sombra individual Novo #5988 $ 1.700,00

Novo labial gloss juice lips #6095 $ 2.000,00

Novo balsamo labial hidratante frutal #5414 $ 2.500,00

Novo base cussion + polvo compacto 2en1 #6090 $ 7.000,00

Novo mascara de pestañas doble cepillos #6071 $ 2.900,00

Novo rubor con esponjita #6098 $ 2.300,00

Novo paleta de sombra 4 colores #5815 $ 3.400,00

Novo polvo compacto #5935 $ 3.400,00

Novo Paleta maquillaje + rubor #6089 $ 4.200,00

Novo lips stick Gloss #5989 $ 3.000,00
`;

// Procesar el texto para extraer productos
const lineas = texto.split('\n');
let tipoActual = '';
let marcaActual = '';
const productos = [];

for (const linea of lineas) {
  const lineaTrimmed = linea.trim();
  
  // Saltar líneas vacías
  if (!lineaTrimmed) continue;
  
  // Detectar tipo
  if (lineaTrimmed.startsWith('Tipo:')) {
    tipoActual = lineaTrimmed.replace('Tipo:', '').trim();
    continue;
  }
  
  // Detectar marca
  if (lineaTrimmed.startsWith('Marca:') || lineaTrimmed.startsWith('marca:')) {
    marcaActual = lineaTrimmed.replace(/Marca:|marca:/, '').trim();
    continue;
  }
  
  // Procesar producto
  const precioMatch = lineaTrimmed.match(/\$\s*[\d.,]+/);
  if (precioMatch) {
    const precioStr = precioMatch[0];
    const nombre = lineaTrimmed.substring(0, lineaTrimmed.indexOf(precioStr)).trim();
    
    // Ignorar líneas que contienen "agotado"
    if (lineaTrimmed.includes('agotado')) continue;
    
    const precio = limpiarPrecio(precioStr);
    
    // Crear descripción básica
    let descripcion = `${nombre} - ${tipoActual}`;
    if (marcaActual) {
      descripcion += ` de la marca ${marcaActual}`;
    }
    
    // Crear características básicas
    const caracteristicas = [
      `Producto de ${tipoActual}`,
      marcaActual ? `Marca: ${marcaActual}` : 'Producto de calidad'
    ].join('\n');
    
    // Crear modo de uso básico
    let modoUso = '';
    if (tipoActual === 'Skincare') {
      modoUso = 'Aplicar sobre la piel limpia y seca. Usar diariamente para mejores resultados.';
    } else if (tipoActual === 'Maquillaje') {
      modoUso = 'Aplicar según necesidad. Retirar con desmaquillante al final del día.';
    } else {
      modoUso = 'Usar según necesidad.';
    }
    
    productos.push({
      nombre,
      precio,
      cuotas: 3,
      precio_cuota: Math.round((precio / 3) * 100) / 100,
      imagen: generarNombreImagen(nombre),
      categoria: tipoActual.toLowerCase(),
      descripcion,
      caracteristicas,
      modo_uso: modoUso,
      calificacion: 5.0,
      num_calificaciones: 0
    });
  }
}

// Generar CSV
let csv = 'nombre,precio,cuotas,imagen,categoria,descripcion,caracteristicas,modo_uso,calificacion,num_calificaciones\n';

for (const producto of productos) {
  csv += `"${producto.nombre}",${producto.precio},${producto.cuotas},"${producto.imagen}","${producto.categoria}","${producto.descripcion}","${producto.caracteristicas}","${producto.modo_uso}",${producto.calificacion},${producto.num_calificaciones}\n`;
}

// Guardar el CSV
fs.writeFileSync('productos_bear_shop.csv', csv);

console.log(`Se han procesado ${productos.length} productos y se ha generado el archivo 'productos_bear_shop.csv'.`);
console.log('Puedes abrir este archivo con Excel y luego guardarlo como CSV para importarlo a tu tienda.');

// Mostrar los primeros 5 productos como ejemplo
console.log('\nEjemplos de productos procesados:');
productos.slice(0, 5).forEach((p, i) => {
  console.log(`\nProducto ${i+1}:`);
  console.log(`Nombre: ${p.nombre}`);
  console.log(`Precio: ${p.precio}`);
  console.log(`Categoría: ${p.categoria}`);
  console.log(`Imagen: ${p.imagen}`);
});