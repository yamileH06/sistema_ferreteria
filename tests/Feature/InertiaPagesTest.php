<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class InertiaPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_resolves_its_inertia_component(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Welcome'));
    }

    public function test_inventory_page_renders_with_its_data(): void
    {
        $this->get('/inventario')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Inventario')
                ->hasAll(['productos', 'categorias', 'proveedores']));
    }

    public function test_point_of_sale_page_renders_with_its_data(): void
    {
        $this->get('/pos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Pos')
                ->hasAll(['productos', 'categorias', 'clientes']));
    }

    public function test_sidebar_destination_pages_render_without_not_found_responses(): void
    {
        foreach (['/proveedores', '/clientes'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page
                    ->component('Ferreteria/Consulta'));
        }
    }

    public function test_categories_page_renders_its_management_component(): void
    {
        $this->get('/categorias')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Categorias')
                ->has('categorias'));
    }

    public function test_sales_history_page_renders_its_dedicated_component(): void
    {
        $this->get('/ventas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/HistorialVentas')
                ->hasAll(['ventas', 'totales', 'filtros', 'pagination']));
    }
}
