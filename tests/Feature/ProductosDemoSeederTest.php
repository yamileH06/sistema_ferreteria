<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Database\Seeders\ProductosDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProductosDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_populates_each_target_category_and_is_idempotent(): void
    {
        $seeder = new ProductosDemoSeeder;
        $seeder->run();

        $categoryNames = [
            'Herramientas Manuales',
            'Herramientas Eléctricas',
            'Materiales de Construcción',
            'Pinturas e Impermeabilizantes',
            'Plomería y Tuberías',
            'Electricidad y Cableado',
            'Cerrajería y Seguridad',
        ];

        foreach ($categoryNames as $name) {
            $category = Categoria::query()
                ->withCount('productos')
                ->where('nombre', $name)
                ->firstOrFail();

            $this->assertSame(4, $category->productos_count);
            $this->assertSame(
                4,
                Producto::query()
                    ->where('categoria_id', $category->id)
                    ->where('codigo_sku', 'like', 'DEMO-%')
                    ->count(),
            );
        }

        $this->get('/categorias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Categorias')
                ->where('categorias.0.productos_count', 4)
                ->where('categorias.0.productos.0.codigo_sku', 'DEMO-CS-001'));

        $seeder->run();

        $this->assertDatabaseCount('productos', 28);
    }
}
