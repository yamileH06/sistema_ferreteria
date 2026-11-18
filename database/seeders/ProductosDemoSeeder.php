<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductosDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $categories = [];

            foreach ($this->categoryNames() as $name) {
                $category = Categoria::query()->firstOrCreate(['nombre' => $name]);
                $categories[$name] = $category->id;
            }

            foreach ($this->products() as $attributes) {
                $product = Producto::query()->firstOrCreate(
                    ['codigo_sku' => $attributes['sku']],
                    [
                        'nombre' => $attributes['nombre'],
                        'categoria_id' => $categories[$attributes['categoria']],
                        'stock' => $attributes['stock'],
                        'precio_compra' => $attributes['precio_compra'],
                        'precio_venta' => $attributes['precio_venta'],
                    ],
                );

                if ($product->categoria_id !== $categories[$attributes['categoria']]) {
                    throw new RuntimeException(
                        "El SKU {$attributes['sku']} ya existe asociado a otra categoría; no se modificaron productos."
                    );
                }
            }
        });
    }

    /**
     * @return list<string>
     */
    private function categoryNames(): array
    {
        return [
            'Herramientas Manuales',
            'Herramientas Eléctricas',
            'Materiales de Construcción',
            'Pinturas e Impermeabilizantes',
            'Plomería y Tuberías',
            'Electricidad y Cableado',
            'Cerrajería y Seguridad',
        ];
    }

    /**
     * @return list<array{sku: string, nombre: string, categoria: string, stock: int, precio_compra: int, precio_venta: int}>
     */
    private function products(): array
    {
        return [
            ['sku' => 'DEMO-HM-001', 'nombre' => 'Martillo de uña de acero forjado 16 oz', 'categoria' => 'Herramientas Manuales', 'stock' => 28, 'precio_compra' => 22000, 'precio_venta' => 28500],
            ['sku' => 'DEMO-HM-002', 'nombre' => 'Alicate universal aislado de 8 pulgadas', 'categoria' => 'Herramientas Manuales', 'stock' => 32, 'precio_compra' => 18000, 'precio_venta' => 24000],
            ['sku' => 'DEMO-HM-003', 'nombre' => 'Juego de destornilladores de 6 piezas', 'categoria' => 'Herramientas Manuales', 'stock' => 24, 'precio_compra' => 30000, 'precio_venta' => 39000],
            ['sku' => 'DEMO-HM-004', 'nombre' => 'Flexómetro resistente de 5 metros', 'categoria' => 'Herramientas Manuales', 'stock' => 40, 'precio_compra' => 15000, 'precio_venta' => 19500],

            ['sku' => 'DEMO-HE-001', 'nombre' => 'Taladro percutor de 650 W', 'categoria' => 'Herramientas Eléctricas', 'stock' => 12, 'precio_compra' => 145000, 'precio_venta' => 188500],
            ['sku' => 'DEMO-HE-002', 'nombre' => 'Pulidora angular de 4 1/2 pulgadas 750 W', 'categoria' => 'Herramientas Eléctricas', 'stock' => 10, 'precio_compra' => 165000, 'precio_venta' => 214500],
            ['sku' => 'DEMO-HE-003', 'nombre' => 'Sierra circular eléctrica de 7 1/4 pulgadas', 'categoria' => 'Herramientas Eléctricas', 'stock' => 8, 'precio_compra' => 250000, 'precio_venta' => 325000],
            ['sku' => 'DEMO-HE-004', 'nombre' => 'Lijadora orbital eléctrica de 240 W', 'categoria' => 'Herramientas Eléctricas', 'stock' => 9, 'precio_compra' => 110000, 'precio_venta' => 143000],

            ['sku' => 'DEMO-MC-001', 'nombre' => 'Bulto de cemento gris de uso general 50 kg', 'categoria' => 'Materiales de Construcción', 'stock' => 60, 'precio_compra' => 25000, 'precio_venta' => 32000],
            ['sku' => 'DEMO-MC-002', 'nombre' => 'Bulto de mortero seco para pega 25 kg', 'categoria' => 'Materiales de Construcción', 'stock' => 35, 'precio_compra' => 16000, 'precio_venta' => 21000],
            ['sku' => 'DEMO-MC-003', 'nombre' => 'Malla electrosoldada para placa 2 x 3 metros', 'categoria' => 'Materiales de Construcción', 'stock' => 18, 'precio_compra' => 52000, 'precio_venta' => 68000],
            ['sku' => 'DEMO-MC-004', 'nombre' => 'Varilla corrugada de 3/8 de pulgada', 'categoria' => 'Materiales de Construcción', 'stock' => 45, 'precio_compra' => 24000, 'precio_venta' => 31000],

            ['sku' => 'DEMO-PA-001', 'nombre' => 'Pintura vinilo tipo 1 blanca galón', 'categoria' => 'Pinturas e Impermeabilizantes', 'stock' => 16, 'precio_compra' => 62000, 'precio_venta' => 80000],
            ['sku' => 'DEMO-PA-002', 'nombre' => 'Esmalte sintético negro cuarto', 'categoria' => 'Pinturas e Impermeabilizantes', 'stock' => 20, 'precio_compra' => 18000, 'precio_venta' => 24000],
            ['sku' => 'DEMO-PA-003', 'nombre' => 'Impermeabilizante acrílico blanco galón', 'categoria' => 'Pinturas e Impermeabilizantes', 'stock' => 11, 'precio_compra' => 28000, 'precio_venta' => 37000],
            ['sku' => 'DEMO-PA-004', 'nombre' => 'Brocha profesional de 3 pulgadas', 'categoria' => 'Pinturas e Impermeabilizantes', 'stock' => 26, 'precio_compra' => 15500, 'precio_venta' => 20500],

            ['sku' => 'DEMO-PL-001', 'nombre' => 'Tubo PVC presión de 1/2 pulgada x 6 metros', 'categoria' => 'Plomería y Tuberías', 'stock' => 30, 'precio_compra' => 22000, 'precio_venta' => 29000],
            ['sku' => 'DEMO-PL-002', 'nombre' => 'Grifería sencilla para lavamanos', 'categoria' => 'Plomería y Tuberías', 'stock' => 14, 'precio_compra' => 14000, 'precio_venta' => 18500],
            ['sku' => 'DEMO-PL-003', 'nombre' => 'Codo PVC presión de 1/2 pulgada', 'categoria' => 'Plomería y Tuberías', 'stock' => 50, 'precio_compra' => 9000, 'precio_venta' => 12000],
            ['sku' => 'DEMO-PL-004', 'nombre' => 'Válvula de bola PVC de 1/2 pulgada', 'categoria' => 'Plomería y Tuberías', 'stock' => 20, 'precio_compra' => 38000, 'precio_venta' => 50000],

            ['sku' => 'DEMO-EL-001', 'nombre' => 'Interruptor sencillo blanco de 10 A', 'categoria' => 'Electricidad y Cableado', 'stock' => 40, 'precio_compra' => 6500, 'precio_venta' => 8500],
            ['sku' => 'DEMO-EL-002', 'nombre' => 'Cable eléctrico de cobre calibre 12 por metro', 'categoria' => 'Electricidad y Cableado', 'stock' => 35, 'precio_compra' => 25000, 'precio_venta' => 33000],
            ['sku' => 'DEMO-EL-003', 'nombre' => 'Tomacorriente doble con polo a tierra', 'categoria' => 'Electricidad y Cableado', 'stock' => 28, 'precio_compra' => 14500, 'precio_venta' => 19000],
            ['sku' => 'DEMO-EL-004', 'nombre' => 'Reflector LED para exterior de 50 W', 'categoria' => 'Electricidad y Cableado', 'stock' => 15, 'precio_compra' => 48000, 'precio_venta' => 63000],

            ['sku' => 'DEMO-CS-001', 'nombre' => 'Candado de bronce de 50 mm alta seguridad', 'categoria' => 'Cerrajería y Seguridad', 'stock' => 16, 'precio_compra' => 21000, 'precio_venta' => 35000],
            ['sku' => 'DEMO-CS-002', 'nombre' => 'Cerradura de sobreponer para puerta', 'categoria' => 'Cerrajería y Seguridad', 'stock' => 12, 'precio_compra' => 42000, 'precio_venta' => 64000],
            ['sku' => 'DEMO-CS-003', 'nombre' => 'Cerrojo de seguridad para puerta', 'categoria' => 'Cerrajería y Seguridad', 'stock' => 10, 'precio_compra' => 28000, 'precio_venta' => 37000],
            ['sku' => 'DEMO-CS-004', 'nombre' => 'Pasador metálico galvanizado de 4 pulgadas', 'categoria' => 'Cerrajería y Seguridad', 'stock' => 18, 'precio_compra' => 9500, 'precio_venta' => 12500],
        ];
    }
}
