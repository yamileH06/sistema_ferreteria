<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;

class CategoriaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Limpiar únicamente los registros genéricos anteriores ("Articulo X de...")
        Producto::where('nombre', 'like', 'Articulo % de %')
            ->orWhere('nombre', 'like', 'Producto % de %')
            ->delete();

        // 2. Proveedor por defecto
        $proveedor = Proveedor::firstOrCreate(
            ['nit' => '900999999-1'],
            ['nombre' => 'Distribuidora Ferretera Mayorista', 'telefono' => '3101234567']
        );

        // 3. Catálogo realista de ferretería colombiana (10 productos por categoría con precios reales)
        $catalogo = [
            'Cerrajería y Seguridad' => [
                ['sku' => 'CERR-101', 'nombre' => 'Candado de Bronce 50mm (Unidad)', 'compra' => 19000, 'venta' => 28000, 'stock' => 24],
                ['sku' => 'CERR-102', 'nombre' => 'Cerradura de Sobreponer Doble Perno (Unidad)', 'compra' => 45000, 'venta' => 65000, 'stock' => 15],
                ['sku' => 'CERR-103', 'nombre' => 'Bisagra de Acero 3x3 Pulgadas (Par)', 'compra' => 3500, 'venta' => 6000, 'stock' => 60],
                ['sku' => 'CERR-104', 'nombre' => 'Cerrojo de Seguridad Tipo Pasador (Unidad)', 'compra' => 12000, 'venta' => 18500, 'stock' => 30],
                ['sku' => 'CERR-105', 'nombre' => 'Cadena Galvanizada 3/16 (Metro)', 'compra' => 4500, 'venta' => 7500, 'stock' => 80],
                ['sku' => 'CERR-106', 'nombre' => 'Cerradura Tubular Pomo Acero Baño (Unidad)', 'compra' => 22000, 'venta' => 34000, 'stock' => 20],
                ['sku' => 'CERR-107', 'nombre' => 'Manija Colonial Negro Mate (Par)', 'compra' => 28000, 'venta' => 42000, 'stock' => 14],
                ['sku' => 'CERR-108', 'nombre' => 'Pestillo Portacandado Reforzado 4" (Unidad)', 'compra' => 4000, 'venta' => 7000, 'stock' => 45],
                ['sku' => 'CERR-109', 'nombre' => 'Ojo Mágico Gran Angular Cromado (Unidad)', 'compra' => 8500, 'venta' => 14000, 'stock' => 25],
                ['sku' => 'CERR-110', 'nombre' => 'Caja Fuerte Digital Empotrable (Unidad)', 'compra' => 185000, 'venta' => 255000, 'stock' => 6],
            ],
            'Electricidad y Cableado' => [
                ['sku' => 'ELEC-101', 'nombre' => 'Cable Eléctrico Encauchetado #12 (Metro)', 'compra' => 3200, 'venta' => 4800, 'stock' => 200],
                ['sku' => 'ELEC-102', 'nombre' => 'Interruptor Sencillo con Placa Blanca (Unidad)', 'compra' => 4500, 'venta' => 7500, 'stock' => 50],
                ['sku' => 'ELEC-103', 'nombre' => 'Tomacorriente Doble Polo a Tierra (Unidad)', 'compra' => 5500, 'venta' => 9000, 'stock' => 55],
                ['sku' => 'ELEC-104', 'nombre' => 'Bombillo LED 12W Luz Blanca Rosca E27 (Unidad)', 'compra' => 4200, 'venta' => 7000, 'stock' => 90],
                ['sku' => 'ELEC-105', 'nombre' => 'Breaker Termomagnético Enchufable 20A (Unidad)', 'compra' => 14000, 'venta' => 21000, 'stock' => 35],
                ['sku' => 'ELEC-106', 'nombre' => 'Cinta Aislante Negra 18m x 19mm (Rollo)', 'compra' => 2800, 'venta' => 4500, 'stock' => 75],
                ['sku' => 'ELEC-107', 'nombre' => 'Canaleta Plástica con Adhesivo 20x12mm (Tramo 2m)', 'compra' => 4000, 'venta' => 6500, 'stock' => 40],
                ['sku' => 'ELEC-108', 'nombre' => 'Plafón de Porcelana E27 Redondo (Unidad)', 'compra' => 2200, 'venta' => 3800, 'stock' => 65],
                ['sku' => 'ELEC-109', 'nombre' => 'Extensión Eléctrica Reforzada 5 Metros (Unidad)', 'compra' => 16000, 'venta' => 24000, 'stock' => 22],
                ['sku' => 'ELEC-110', 'nombre' => 'Multitoma Supresor de Picos 6 Salidas (Unidad)', 'compra' => 21000, 'venta' => 32000, 'stock' => 18],
            ],
            'Herramientas Eléctricas' => [
                ['sku' => 'HELE-101', 'nombre' => 'Taladro Percutor 1/2" 650W (Unidad)', 'compra' => 145000, 'venta' => 198000, 'stock' => 12],
                ['sku' => 'HELE-102', 'nombre' => 'Pulidora Angular 4-1/2" 850W (Unidad)', 'compra' => 165000, 'venta' => 225000, 'stock' => 10],
                ['sku' => 'HELE-103', 'nombre' => 'Sierra Circular 7-1/4" 1400W (Unidad)', 'compra' => 260000, 'venta' => 345000, 'stock' => 7],
                ['sku' => 'HELE-104', 'nombre' => 'Rotomartillo SDS Plus 800W (Unidad)', 'compra' => 290000, 'venta' => 389000, 'stock' => 6],
                ['sku' => 'HELE-105', 'nombre' => 'Sierra Caladora Velocidad Variable 550W (Unidad)', 'compra' => 135000, 'venta' => 185000, 'stock' => 9],
                ['sku' => 'HELE-106', 'nombre' => 'Lijadora Orbital 1/4 Hoja 240W (Unidad)', 'compra' => 115000, 'venta' => 159000, 'stock' => 11],
                ['sku' => 'HELE-107', 'nombre' => 'Pistola de Calor 2 Temperaturas 1800W (Unidad)', 'compra' => 110000, 'venta' => 149000, 'stock' => 8],
                ['sku' => 'HELE-108', 'nombre' => 'Taladro Atornillador Inalámbrico 12V (Unidad)', 'compra' => 155000, 'venta' => 215000, 'stock' => 14],
                ['sku' => 'HELE-109', 'nombre' => 'Esmeril de Banco 6" 370W (Unidad)', 'compra' => 195000, 'venta' => 265000, 'stock' => 5],
                ['sku' => 'HELE-110', 'nombre' => 'Soldador Inversor Mini 160A 110/220V (Unidad)', 'compra' => 340000, 'venta' => 455000, 'stock' => 6],
            ],
            'Herramientas Manuales' => [
                ['sku' => 'HMAN-101', 'nombre' => 'Martillo de Uña Cabo Fibra 16oz (Unidad)', 'compra' => 21000, 'venta' => 32000, 'stock' => 28],
                ['sku' => 'HMAN-102', 'nombre' => 'Juego Destornilladores Pala y Estrella x6 (Set)', 'compra' => 18000, 'venta' => 27000, 'stock' => 25],
                ['sku' => 'HMAN-103', 'nombre' => 'Alicate Universal Electricista 8" (Unidad)', 'compra' => 16500, 'venta' => 25000, 'stock' => 30],
                ['sku' => 'HMAN-104', 'nombre' => 'Flexómetro Cinta Métrica 5 Metros (Unidad)', 'compra' => 7500, 'venta' => 12500, 'stock' => 45],
                ['sku' => 'HMAN-105', 'nombre' => 'Llave de Expansión Cromada 10" (Unidad)', 'compra' => 24000, 'venta' => 36000, 'stock' => 18],
                ['sku' => 'HMAN-106', 'nombre' => 'Marco de Segueta Tubular Profesional (Unidad)', 'compra' => 15000, 'venta' => 23000, 'stock' => 22],
                ['sku' => 'HMAN-107', 'nombre' => 'Juego Llaves Hexagonales Bristol x9 (Set)', 'compra' => 11000, 'venta' => 17500, 'stock' => 32],
                ['sku' => 'HMAN-108', 'nombre' => 'Nivel de Burbuja Aluminio 14" (Unidad)', 'compra' => 14000, 'venta' => 21500, 'stock' => 20],
                ['sku' => 'HMAN-109', 'nombre' => 'Hombre Solo Mordaza Curva 10" (Unidad)', 'compra' => 23000, 'venta' => 35000, 'stock' => 24],
                ['sku' => 'HMAN-110', 'nombre' => 'Bisturí Metálico Retráctil 18mm (Unidad)', 'compra' => 4500, 'venta' => 7500, 'stock' => 50],
            ],
            'Materiales de Construcción' => [
                ['sku' => 'CONS-101', 'nombre' => 'Cemento Gris Uso General (Bulto 50kg)', 'compra' => 28500, 'venta' => 34000, 'stock' => 120],
                ['sku' => 'CONS-102', 'nombre' => 'Varilla Corrugada Acero 3/8" (Tramo 6m)', 'compra' => 19500, 'venta' => 25500, 'stock' => 150],
                ['sku' => 'CONS-103', 'nombre' => 'Arena de Peña Lavada (Bulto 40kg)', 'compra' => 5500, 'venta' => 8500, 'stock' => 90],
                ['sku' => 'CONS-104', 'nombre' => 'Gravilla Triturada 3/4" (Bulto 40kg)', 'compra' => 6000, 'venta' => 9000, 'stock' => 85],
                ['sku' => 'CONS-105', 'nombre' => 'Ladrillo Tolete Común Arcilla (Unidad)', 'compra' => 850, 'venta' => 1300, 'stock' => 800],
                ['sku' => 'CONS-106', 'nombre' => 'Bloque Estructural Concreto #5 (Unidad)', 'compra' => 2600, 'venta' => 3800, 'stock' => 400],
                ['sku' => 'CONS-107', 'nombre' => 'Alambre Negro Recocido Calibre 18 (Kilo)', 'compra' => 5800, 'venta' => 8500, 'stock' => 70],
                ['sku' => 'CONS-108', 'nombre' => 'Estuco Plástico Listo para Aplicar (Cuñete)', 'compra' => 32000, 'venta' => 44000, 'stock' => 25],
                ['sku' => 'CONS-109', 'nombre' => 'Cal Hidratada Construcción (Bulto 25kg)', 'compra' => 14000, 'venta' => 19500, 'stock' => 45],
                ['sku' => 'CONS-110', 'nombre' => 'Impermeabilizante Integral Mortero (Garrafa 4kg)', 'compra' => 26000, 'venta' => 36500, 'stock' => 20],
            ],
            'Pinturas e Impermeabilizantes' => [
                ['sku' => 'PINT-101', 'nombre' => 'Pintura Vinilo Tipo 1 Blanco (Galón)', 'compra' => 48000, 'venta' => 66000, 'stock' => 30],
                ['sku' => 'PINT-102', 'nombre' => 'Esmalte Sintético Brillante Negro (1/4 Galón)', 'compra' => 16500, 'venta' => 23500, 'stock' => 35],
                ['sku' => 'PINT-103', 'nombre' => 'Impermeabilizante Acrílico Techos (Cuñete 5 Gal)', 'compra' => 185000, 'venta' => 245000, 'stock' => 10],
                ['sku' => 'PINT-104', 'nombre' => 'Brocha Cerda Natural Profesional 3" (Unidad)', 'compra' => 6500, 'venta' => 10500, 'stock' => 48],
                ['sku' => 'PINT-105', 'nombre' => 'Rodillo Felpa Antigota 9" con Maneral (Unidad)', 'compra' => 11000, 'venta' => 16500, 'stock' => 32],
                ['sku' => 'PINT-106', 'nombre' => 'Lija de Agua Grano 150 (Pliego Unidad)', 'compra' => 1200, 'venta' => 2000, 'stock' => 150],
                ['sku' => 'PINT-107', 'nombre' => 'Disolvente Thinner Corriente (Botella 750ml)', 'compra' => 5500, 'venta' => 8500, 'stock' => 40],
                ['sku' => 'PINT-108', 'nombre' => 'Resanador Acrílico Grietas Pared (1/4 Galón)', 'compra' => 9500, 'venta' => 14500, 'stock' => 28],
                ['sku' => 'PINT-109', 'nombre' => 'Pintura Anticorrosiva Gris Metal (Galón)', 'compra' => 44000, 'venta' => 61000, 'stock' => 18],
                ['sku' => 'PINT-110', 'nombre' => 'Cinta de Enmascarar 24mm x 40m (Rollo)', 'compra' => 4200, 'venta' => 6800, 'stock' => 60],
            ],
            'Plomería y Tubería' => [
                ['sku' => 'PLOM-101', 'nombre' => 'Tubo PVC Presión 1/2" RDE 21 (Tramo 6m)', 'compra' => 14500, 'venta' => 21000, 'stock' => 65],
                ['sku' => 'PLOM-102', 'nombre' => 'Soldadura Líquida PVC (Tarro 1/4 Galón)', 'compra' => 18000, 'venta' => 26000, 'stock' => 25],
                ['sku' => 'PLOM-103', 'nombre' => 'Válvula de Bola Lisa PVC 1/2" (Unidad)', 'compra' => 5200, 'venta' => 8500, 'stock' => 50],
                ['sku' => 'PLOM-104', 'nombre' => 'Codo PVC Presión 90° 1/2" (Unidad)', 'compra' => 700, 'venta' => 1500, 'stock' => 200],
                ['sku' => 'PLOM-105', 'nombre' => 'Tee PVC Presión Lisa 1/2" (Unidad)', 'compra' => 900, 'venta' => 1800, 'stock' => 180],
                ['sku' => 'PLOM-106', 'nombre' => 'Cinta Selladora Teflón 1/2" x 10m (Rollo)', 'compra' => 1100, 'venta' => 2200, 'stock' => 120],
                ['sku' => 'PLOM-107', 'nombre' => 'Sifón Flexible Extensible Lavamanos (Unidad)', 'compra' => 8500, 'venta' => 13500, 'stock' => 35],
                ['sku' => 'PLOM-108', 'nombre' => 'Llave Terminal Jardín Bronce 1/2" (Unidad)', 'compra' => 13000, 'venta' => 19500, 'stock' => 28],
                ['sku' => 'PLOM-109', 'nombre' => 'Válvula Flotador Tanque Agua 1/2" (Unidad)', 'compra' => 16000, 'venta' => 24000, 'stock' => 20],
                ['sku' => 'PLOM-110', 'nombre' => 'Acople Flexible Lavamanos 1/2" x 40cm (Unidad)', 'compra' => 6500, 'venta' => 10500, 'stock' => 42],
            ],
        ];

        foreach ($catalogo as $nombreCat => $productos) {
            // Busca ignorando mayúsculas/minúsculas para reutilizar tus categorías actuales
            $categoria = Categoria::whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreCat)])->first();
            if (!$categoria) {
                $categoria = Categoria::create(['nombre' => $nombreCat]);
            }

            foreach ($productos as $item) {
                $datos = [
                    'nombre' => $item['nombre'],
                    'categoria_id' => $categoria->id,
                    'stock' => $item['stock'],
                    'precio_compra' => $item['compra'],
                    'precio_venta' => $item['venta'],
                ];

                if (Schema::hasColumn('productos', 'proveedor_id')) {
                    $datos['proveedor_id'] = $proveedor->id;
                }

                Producto::updateOrCreate(
                    ['codigo_sku' => $item['sku']],
                    $datos
                );
            }
        }
    }
}