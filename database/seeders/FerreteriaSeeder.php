<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;
use App\Models\Proveedor;
use App\Models\Producto;

class FerreteriaSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Categorías
        $catHerramientas = Categoria::create(['nombre' => 'Herramientas', 'descripcion' => 'Herramientas manuales y eléctricas']);
        $catConstruccion = Categoria::create(['nombre' => 'Construcción', 'descripcion' => 'Materiales pesados y obra gris']);
        $catPinturas = Categoria::create(['nombre' => 'Pinturas y Acabados', 'descripcion' => 'Pinturas, impermeabilizantes y solventes']);
        $catPlomeria = Categoria::create(['nombre' => 'Plomería', 'descripcion' => 'Tubos, llaves y accesorios PVC']);

        // 2. Crear Proveedores
        $provGlobal = Proveedor::create(['nombre' => 'Ferretería Global S.A.S.', 'nit' => '900123456-1', 'telefono' => '3001234567']);
        $provHerra = Proveedor::create(['nombre' => 'Herramientas de Colombia', 'nit' => '800987654-2', 'telefono' => '3107654321']);

        // 3. Crear Productos de Ferretería (para llenar la cuadrícula del POS)
        Producto::create([
            'nombre' => 'Martillo de Uña Acero Forjado',
            'codigo_sku' => 'HERM-01',
            'categoria_id' => $catHerramientas->id,
            'proveedor_id' => $provHerra->id,
            'stock' => 15,
            'precio_compra' => 22000,
            'precio_venta' => 38000
        ]);

        Producto::create([
            'nombre' => 'Taladro Percutor 650W',
            'codigo_sku' => 'HERM-02',
            'categoria_id' => $catHerramientas->id,
            'proveedor_id' => $provHerra->id,
            'stock' => 8,
            'precio_compra' => 140000,
            'precio_venta' => 195000
        ]);

        Producto::create([
            'nombre' => 'Cinta Métrica Flexómetro 5m',
            'codigo_sku' => 'HERM-03',
            'categoria_id' => $catHerramientas->id,
            'proveedor_id' => $provHerra->id,
            'stock' => 25,
            'precio_compra' => 8000,
            'precio_venta' => 15000
        ]);

        Producto::create([
            'nombre' => 'Bolsa de Cemento Gris 50kg',
            'codigo_sku' => 'CONS-01',
            'categoria_id' => $catConstruccion->id,
            'proveedor_id' => $provGlobal->id,
            'stock' => 40,
            'precio_compra' => 28000,
            'precio_venta' => 34000
        ]);

        Producto::create([
            'nombre' => 'Pintura Vinilo TIPO 1 Galón Blanco',
            'codigo_sku' => 'PINT-01',
            'categoria_id' => $catPinturas->id,
            'proveedor_id' => $provGlobal->id,
            'stock' => 12,
            'precio_compra' => 45000,
            'precio_venta' => 65000
        ]);

        Producto::create([
            'nombre' => 'Tubo PVC Presión 1/2 pulgada',
            'codigo_sku' => 'PLOM-01',
            'categoria_id' => $catPlomeria->id,
            'proveedor_id' => $provGlobal->id,
            'stock' => 30,
            'precio_compra' => 7000,
            'precio_venta' => 12000
        ]);
    }
}