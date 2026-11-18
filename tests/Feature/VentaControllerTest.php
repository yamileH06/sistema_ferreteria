<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VentaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_cajero_can_register_a_sale_with_fractional_quantity_and_download_its_receipt(): void
    {
        $cashier = User::factory()->create(['rol' => 'cajero']);
        $product = $this->createProduct(stock: '2.500', price: '2500.00');

        $response = $this->actingAs($cashier)->post('/pos/cobrar', [
            'cliente_id' => null,
            'metodo_pago' => 'Efectivo',
            'items' => [
                ['producto_id' => $product->id, 'cantidad' => '1.250'],
            ],
        ]);

        $response->assertRedirect(route('pos.index'));
        $sale = Venta::query()->sole();

        $this->assertSame('VTA-00000001', $sale->codigo_venta);
        $this->assertSame('3125.00', $sale->total);
        $this->assertSame('3125.00', $sale->subtotal);
        $this->assertSame('0.00', $sale->descuento);
        $this->assertSame('COP', $sale->moneda);
        $this->assertSame('1.250', $sale->detalles()->sole()->cantidad);
        $this->assertSame('1.250', $product->fresh()->stock);

        $this->get(route('ventas.factura', $sale))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_sale_at_discount_threshold_has_no_discount(): void
    {
        $cashier = User::factory()->create(['rol' => 'cajero']);
        $product = $this->createProduct(stock: '2.000', price: '2000000.00');

        $this->actingAs($cashier)->post('/pos/cobrar', [
            'metodo_pago' => 'Efectivo',
            'items' => [
                ['producto_id' => $product->id, 'cantidad' => '1.000'],
            ],
        ])->assertRedirect(route('pos.index'));

        $sale = Venta::query()->sole();

        $this->assertSame('2000000.00', $sale->subtotal);
        $this->assertSame('0.00', $sale->descuento);
        $this->assertSame('2000000.00', $sale->total);
    }

    public function test_sale_above_discount_threshold_applies_five_percent_discount_server_side(): void
    {
        $cashier = User::factory()->create(['rol' => 'cajero']);
        $product = $this->createProduct(stock: '2.000', price: '2100000.00');

        $this->actingAs($cashier)->post('/pos/cobrar', [
            'metodo_pago' => 'Efectivo',
            'subtotal' => '1.00',
            'descuento' => '0.00',
            'total' => '1.00',
            'items' => [
                ['producto_id' => $product->id, 'cantidad' => '1.000'],
            ],
        ])->assertRedirect(route('pos.index'));

        $sale = Venta::query()->sole();

        $this->assertSame('2100000.00', $sale->subtotal);
        $this->assertSame('105000.00', $sale->descuento);
        $this->assertSame('1995000.00', $sale->total);
    }

    public function test_insufficient_fractional_stock_rejects_the_sale_without_changing_stock_or_sales(): void
    {
        $cashier = User::factory()->create(['rol' => 'cajero']);
        $product = $this->createProduct(stock: '0.500', price: '2500.00');

        $this->actingAs($cashier)
            ->from('/pos')
            ->post('/pos/cobrar', [
                'metodo_pago' => 'Efectivo',
                'items' => [
                    ['producto_id' => $product->id, 'cantidad' => '0.750'],
                ],
            ])
            ->assertRedirect('/pos')
            ->assertSessionHasErrors('items');

        $this->assertDatabaseCount('ventas', 0);
        $this->assertSame('0.500', $product->fresh()->stock);
    }

    public function test_non_cashier_cannot_download_a_sale_receipt(): void
    {
        $user = User::factory()->create(['rol' => 'bodeguero']);
        $sale = Venta::query()->create([
            'user_id' => $user->id,
            'total' => 1000,
            'metodo_pago' => 'Efectivo',
            'estado' => 'completada',
        ]);

        $this->actingAs($user)
            ->get(route('ventas.factura', $sale))
            ->assertRedirect(route('inventario.index'));
    }

    private function createProduct(string $stock, string $price): Producto
    {
        $category = Categoria::query()->create(['nombre' => 'Ferretería']);

        return Producto::query()->create([
            'nombre' => 'Cable eléctrico',
            'codigo_sku' => 'CAB-001',
            'categoria_id' => $category->id,
            'stock' => $stock,
            'precio_compra' => '2000.00',
            'precio_venta' => $price,
        ]);
    }
}
