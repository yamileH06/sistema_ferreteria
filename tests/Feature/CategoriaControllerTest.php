<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_page_includes_product_counts_stock_and_product_details(): void
    {
        $category = Categoria::query()->create(['nombre' => 'Electricidad']);
        Producto::query()->create([
            'nombre' => 'Cable de cobre',
            'codigo_sku' => 'CAB-001',
            'categoria_id' => $category->id,
            'stock' => 12,
            'precio_compra' => 10000,
            'precio_venta' => 15000,
        ]);

        $this->get('/categorias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Categorias')
                ->where('categorias.0.nombre', 'Electricidad')
                ->where('categorias.0.productos_count', 1)
                ->where('categorias.0.stock_disponible', 12)
                ->where('categorias.0.productos.0.codigo_sku', 'CAB-001')
                ->where('categorias.0.productos.0.precio_compra', 10000)
                ->where('categorias.0.productos.0.precio_venta', 15000));
    }

    public function test_valid_category_is_created(): void
    {
        $this->post('/categorias', ['nombre' => 'Plomería'])
            ->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categorias', ['nombre' => 'Plomería']);
    }

    public function test_category_name_is_required(): void
    {
        $this->from('/categorias')
            ->post('/categorias', ['nombre' => ''])
            ->assertRedirect('/categorias')
            ->assertSessionHasErrors(['nombre' => 'The nombre field is required.']);

        $this->assertDatabaseCount('categorias', 0);
    }

    public function test_duplicate_category_name_is_rejected(): void
    {
        Categoria::query()->create(['nombre' => 'Pinturas']);

        $this->from('/categorias')
            ->post('/categorias', ['nombre' => 'Pinturas'])
            ->assertRedirect('/categorias')
            ->assertSessionHasErrors(['nombre' => 'The nombre has already been taken.']);

        $this->assertDatabaseCount('categorias', 1);
    }

    public function test_category_name_can_be_updated_without_changing_its_products(): void
    {
        $category = Categoria::query()->create(['nombre' => 'Eléctricos']);
        $product = Producto::query()->create([
            'nombre' => 'Interruptor',
            'codigo_sku' => 'INT-001',
            'categoria_id' => $category->id,
            'stock' => 3,
            'precio_compra' => 5000,
            'precio_venta' => 8000,
        ]);

        $this->put("/categorias/{$category->id}", ['nombre' => 'Electricidad'])
            ->assertRedirect(route('categorias.index'));

        $this->assertDatabaseHas('categorias', [
            'id' => $category->id,
            'nombre' => 'Electricidad',
        ]);
        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'categoria_id' => $category->id,
        ]);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $category = Categoria::query()->create(['nombre' => 'Construcción']);
        $product = Producto::query()->create([
            'nombre' => 'Cemento',
            'codigo_sku' => 'CEM-001',
            'categoria_id' => $category->id,
            'stock' => 5,
            'precio_compra' => 25000,
            'precio_venta' => 32000,
        ]);

        $this->delete("/categorias/{$category->id}")
            ->assertRedirect(route('categorias.index'))
            ->assertSessionHasErrors([
                'categoria' => 'No se puede eliminar esta categoría porque tiene productos asociados.',
            ]);

        $this->assertModelExists($category);
        $this->assertModelExists($product);
    }

    public function test_category_without_products_can_be_deleted(): void
    {
        $category = Categoria::query()->create(['nombre' => 'Seguridad']);

        $this->delete("/categorias/{$category->id}")
            ->assertRedirect(route('categorias.index'));

        $this->assertModelMissing($category);
    }
}
