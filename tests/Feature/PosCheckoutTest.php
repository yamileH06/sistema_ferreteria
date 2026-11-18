<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_records_sale_details_and_decrements_stock(): void
    {
        $user = User::factory()->create();
        $category = Categoria::query()->create(['nombre' => 'Herramientas']);
        $client = Cliente::query()->create([
            'nombre' => 'Cliente de prueba',
            'cedula_nit' => '123456789',
        ]);
        $product = $this->createProduct($category, stock: 5);

        $response = $this->actingAs($user)->post('/pos/cobrar', [
            'cliente_id' => $client->id,
            'metodo_pago' => 'Efectivo',
            'items' => [
                ['producto_id' => $product->id, 'cantidad' => 2],
            ],
        ]);

        $response->assertRedirect(route('pos.index'));
        $response->assertSessionHas('success', 'Venta registrada correctamente.');
        $this->assertDatabaseHas('ventas', [
            'cliente_id' => $client->id,
            'user_id' => $user->id,
            'total' => '76000.00',
            'metodo_pago' => 'Efectivo',
        ]);
        $this->assertDatabaseHas('detalle_ventas', [
            'producto_id' => $product->id,
            'cantidad' => 2,
            'precio_unitario' => '38000.00',
            'subtotal' => '76000.00',
        ]);
        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'stock' => 3,
        ]);
    }

    public function test_checkout_rejects_quantity_above_stock_without_writing_sale(): void
    {
        $user = User::factory()->create();
        $category = Categoria::query()->create(['nombre' => 'Herramientas']);
        $product = $this->createProduct($category, stock: 1);

        $response = $this->actingAs($user)->from('/pos')->post('/pos/cobrar', [
            'cliente_id' => null,
            'metodo_pago' => 'Tarjeta',
            'items' => [
                ['producto_id' => $product->id, 'cantidad' => 2],
            ],
        ]);

        $response->assertRedirect('/pos');
        $response->assertSessionHasErrors('items');
        $this->assertDatabaseCount('ventas', 0);
        $this->assertDatabaseCount('detalle_ventas', 0);
        $this->assertDatabaseHas('productos', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }

    public function test_checkout_rejects_payment_method_outside_the_supported_options(): void
    {
        $user = User::factory()->create();
        $category = Categoria::query()->create(['nombre' => 'Herramientas']);
        $product = $this->createProduct($category);

        $response = $this->actingAs($user)->from('/pos')->post('/pos/cobrar', [
            'cliente_id' => null,
            'metodo_pago' => 'Crédito',
            'items' => [
                ['producto_id' => $product->id, 'cantidad' => 1],
            ],
        ]);

        $response->assertRedirect('/pos');
        $response->assertSessionHasErrors('metodo_pago');
        $this->assertDatabaseCount('ventas', 0);
    }

    private function createProduct(Categoria $category, int $stock = 5): Producto
    {
        return Producto::query()->create([
            'nombre' => 'Martillo de prueba',
            'codigo_sku' => 'MART-001',
            'categoria_id' => $category->id,
            'stock' => $stock,
            'precio_compra' => 22000,
            'precio_venta' => 38000,
        ]);
    }
}
