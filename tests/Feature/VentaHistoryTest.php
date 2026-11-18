<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class VentaHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_includes_sale_details_and_filtered_summary_totals(): void
    {
        $client = Cliente::query()->create([
            'nombre' => 'Laura Martínez',
            'cedula_nit' => '1001001001',
        ]);
        $sale = $this->createSale($client, 'Efectivo', 35000);
        $product = $this->createProduct('Taladro percutor', 'TAL-001');
        DetalleVenta::query()->create([
            'venta_id' => $sale->id,
            'producto_id' => $product->id,
            'cantidad' => 1,
            'precio_unitario' => 35000,
            'subtotal' => 35000,
        ]);
        $this->createSale(null, 'Tarjeta', 12000);

        $this->actingAs(User::query()->first())
            ->get('/ventas?q=Laura')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/HistorialVentas')
                ->where('totales.ventas', 35000)
                ->where('totales.efectivo', 35000)
                ->where('ventas.0.id', $sale->id)
                ->where('ventas.0.productos.0.nombre', 'Taladro percutor')
                ->where('ventas.0.productos.0.cantidad', 1)
                ->where('ventas.0.productos.0.precio_unitario', 35000)
                ->where('ventas.0.descuento', '0.00'));
    }

    public function test_history_filters_sales_by_sale_number_date_range_and_payment_method(): void
    {
        $matchingClient = Cliente::query()->create([
            'nombre' => 'Carlos Rosero',
            'cedula_nit' => '1001001002',
        ]);
        $matchingSale = $this->createSale($matchingClient, 'Efectivo', 20000, '2026-11-10 10:00:00');
        $this->createSale($matchingClient, 'Tarjeta', 30000, '2026-11-10 11:00:00');
        $this->createSale(null, 'Efectivo', 40000, '2026-11-12 11:00:00');

        $this->actingAs(User::query()->first())
            ->get('/ventas?q='.$matchingSale->id.'&desde=2026-11-10&hasta=2026-11-10&metodo_pago=Efectivo')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/HistorialVentas')
                ->has('ventas', 1)
                ->where('ventas.0.id', $matchingSale->id)
                ->where('totales.ventas', 20000)
                ->where('totales.efectivo', 20000)
                ->where('filtros.metodo_pago', 'Efectivo'));
    }

    public function test_history_rejects_an_end_date_before_the_start_date(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'cajero']))
            ->from('/ventas')
            ->get('/ventas?desde=2026-11-12&hasta=2026-11-10')
            ->assertRedirect('/ventas')
            ->assertSessionHasErrors('hasta');
    }

    private function createSale(
        ?Cliente $client,
        string $paymentMethod,
        int $total,
        string $createdAt = '2026-11-10 09:00:00',
    ): Venta {
        $user = User::query()->first() ?? User::factory()->create();

        $sale = Venta::query()->create([
            'cliente_id' => $client?->id,
            'user_id' => $user->id,
            'total' => $total,
            'metodo_pago' => $paymentMethod,
            'estado' => 'completada',
        ]);

        $sale->forceFill([
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'fecha_venta' => $createdAt,
        ])->saveQuietly();

        return $sale;
    }

    private function createProduct(string $name, string $sku): Producto
    {
        $category = Categoria::query()->create([
            'nombre' => 'Herramientas '.$sku,
        ]);

        return Producto::query()->create([
            'nombre' => $name,
            'codigo_sku' => $sku,
            'categoria_id' => $category->id,
            'stock' => 5,
            'precio_compra' => 25000,
            'precio_venta' => 35000,
        ]);
    }
}
