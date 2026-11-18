<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProveedorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_bodeguero_can_view_providers_and_filter_fields_are_available(): void
    {
        $provider = Proveedor::query()->create([
            'nombre' => 'Ferretería Andina',
            'nit' => '900123456-1',
            'telefono' => '3123456789',
            'correo' => 'ventas@example.com',
            'direccion' => 'Ipiales, Nariño',
        ]);

        $this->actingAs(User::factory()->create(['rol' => 'bodeguero']))
            ->get('/proveedores')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Proveedores')
                ->where('proveedores.0.id', $provider->id)
                ->where('proveedores.0.correo', 'ventas@example.com'));
    }

    public function test_bodeguero_can_create_and_update_provider_details(): void
    {
        $user = User::factory()->create(['rol' => 'bodeguero']);

        $this->actingAs($user)
            ->from('/proveedores')
            ->post('/proveedores', [
                'nombre' => 'Suministros del Sur',
                'nit' => '901234567-8',
                'telefono' => '3101234567',
                'correo' => 'contacto@example.com',
                'direccion' => 'Carrera 5, Ipiales',
            ])
            ->assertRedirect('/proveedores')
            ->assertSessionHasNoErrors();

        $provider = Proveedor::query()->sole();

        $this->assertSame('contacto@example.com', $provider->correo);
        $this->assertSame('Carrera 5, Ipiales', $provider->direccion);

        $this->from('/proveedores')
            ->put("/proveedores/{$provider->id}", [
                'nombre' => 'Suministros del Sur SAS',
                'nit' => '901234567-8',
                'telefono' => '3101234567',
                'correo' => 'actualizado@example.com',
                'direccion' => 'Calle 10, Ipiales',
            ])
            ->assertRedirect('/proveedores')
            ->assertSessionHasNoErrors();

        $this->assertSame('Suministros del Sur SAS', $provider->fresh()->nombre);
        $this->assertSame('actualizado@example.com', $provider->fresh()->correo);
    }

    public function test_provider_cannot_be_deleted_while_products_reference_it(): void
    {
        $user = User::factory()->create(['rol' => 'bodeguero']);
        $provider = Proveedor::query()->create(['nombre' => 'Proveedor asociado']);
        $category = Categoria::query()->create(['nombre' => 'Herramientas']);
        $product = Producto::query()->create([
            'nombre' => 'Taladro',
            'codigo_sku' => 'TAL-100',
            'categoria_id' => $category->id,
            'proveedor_id' => $provider->id,
            'stock' => 5,
            'precio_compra' => 10000,
            'precio_venta' => 15000,
        ]);

        $this->actingAs($user)
            ->from('/proveedores')
            ->delete("/proveedores/{$provider->id}")
            ->assertRedirect('/proveedores')
            ->assertSessionHasErrors('proveedor');

        $this->assertModelExists($provider);
        $this->assertModelExists($product);
    }

    public function test_cajero_cannot_access_provider_management(): void
    {
        $this->actingAs(User::factory()->create(['rol' => 'cajero']))
            ->get('/proveedores')
            ->assertRedirect(route('pos.index'));
    }

    public function test_bodeguero_can_restock_products_and_stock_increases_with_traceability(): void
    {
        $user = User::factory()->create(['rol' => 'bodeguero']);
        $provider = Proveedor::query()->create(['nombre' => 'Distribuidora Ferretera']);
        $category = Categoria::query()->create(['nombre' => 'Construcción']);

        $product1 = Producto::query()->create([
            'nombre' => 'Cemento Gris 50kg',
            'codigo_sku' => 'CEM-050',
            'categoria_id' => $category->id,
            'proveedor_id' => $provider->id,
            'stock' => 10.000,
            'precio_compra' => 25000,
            'precio_venta' => 32000,
        ]);

        $product2 = Producto::query()->create([
            'nombre' => 'Varilla Corrugada 1/2',
            'codigo_sku' => 'VAR-012',
            'categoria_id' => $category->id,
            'proveedor_id' => null,
            'stock' => 5.500,
            'precio_compra' => 18000,
            'precio_venta' => 24000,
        ]);

        $this->actingAs($user)
            ->from('/proveedores')
            ->post("/proveedores/{$provider->id}/reabastecer", [
                'items' => [
                    [
                        'producto_id' => $product1->id,
                        'cantidad' => 15.000,
                        'costo_unitario' => 26000,
                    ],
                    [
                        'producto_id' => $product2->id,
                        'cantidad' => 4.500,
                        'costo_unitario' => 18500,
                    ],
                ],
                'notas' => 'Factura de compra #FAC-8842',
            ])
            ->assertRedirect('/proveedores')
            ->assertSessionHas('success');

        // Stock del producto 1 debe haber sumado: 10 + 15 = 25
        $this->assertEquals(25.000, (float) $product1->fresh()->stock);
        $this->assertEquals(26000, (float) $product1->fresh()->precio_compra);

        // Stock del producto 2 debe haber sumado: 5.5 + 4.5 = 10
        $this->assertEquals(10.000, (float) $product2->fresh()->stock);
        $this->assertEquals(18500, (float) $product2->fresh()->precio_compra);
        $this->assertSame($provider->id, $product2->fresh()->proveedor_id);

        // Registro de trazabilidad en la tabla reabastecimientos
        $this->assertDatabaseHas('reabastecimientos', [
            'proveedor_id' => $provider->id,
            'producto_id' => $product1->id,
            'user_id' => $user->id,
            'cantidad' => 15.000,
            'costo_unitario' => 26000,
            'notas' => 'Factura de compra #FAC-8842',
        ]);

        $this->assertDatabaseHas('reabastecimientos', [
            'proveedor_id' => $provider->id,
            'producto_id' => $product2->id,
            'user_id' => $user->id,
            'cantidad' => 4.500,
            'costo_unitario' => 18500,
            'notas' => 'Factura de compra #FAC-8842',
        ]);
    }

    public function test_restock_fails_with_invalid_quantities_or_empty_items(): void
    {
        $user = User::factory()->create(['rol' => 'bodeguero']);
        $provider = Proveedor::query()->create(['nombre' => 'Proveedor Test']);
        $category = Categoria::query()->create(['nombre' => 'Pinturas']);
        $product = Producto::query()->create([
            'nombre' => 'Esmalte Sintético',
            'codigo_sku' => 'ESM-001',
            'categoria_id' => $category->id,
            'stock' => 8,
            'precio_compra' => 15000,
            'precio_venta' => 22000,
        ]);

        $this->actingAs($user)
            ->from('/proveedores')
            ->post("/proveedores/{$provider->id}/reabastecer", [
                'items' => [
                    [
                        'producto_id' => $product->id,
                        'cantidad' => 0,
                    ],
                ],
            ])
            ->assertRedirect('/proveedores')
            ->assertSessionHasErrors('items.0.cantidad');

        $this->assertEquals(8, (float) $product->fresh()->stock);
    }
}
