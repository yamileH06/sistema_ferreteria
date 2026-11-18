<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class RolesAndUsuariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_cajero_can_access_pos_but_not_inventory(): void
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);

        $this->actingAs($cajero)
            ->get('/pos')
            ->assertOk();

        $this->actingAs($cajero)
            ->get('/inventario')
            ->assertRedirect(route('pos.index'))
            ->assertSessionHas('error');
    }

    public function test_bodeguero_can_access_inventory_but_not_sales_history(): void
    {
        $bodeguero = User::factory()->create(['rol' => 'bodeguero']);

        $this->actingAs($bodeguero)
            ->get('/inventario')
            ->assertOk();

        $this->actingAs($bodeguero)
            ->get('/ventas')
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHas('error');
    }

    public function test_only_administrators_can_open_user_management(): void
    {
        $cajero = User::factory()->create(['rol' => 'cajero']);

        $this->actingAs($cajero)
            ->get('/usuarios')
            ->assertRedirect(route('pos.index'));

        $this->actingAs(User::factory()->create(['rol' => 'administrador']))
            ->get('/usuarios')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Ferreteria/Usuarios'));
    }

    public function test_administrator_can_create_an_employee_with_a_valid_role(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $this->actingAs($admin)
            ->post('/usuarios', [
                'name' => 'María Cajera',
                'email' => 'maria@example.com',
                'password' => 'secure-password',
                'password_confirmation' => 'secure-password',
                'rol' => 'cajero',
            ])
            ->assertRedirect(route('usuarios.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'rol' => 'cajero',
        ]);
    }

    public function test_administrator_cannot_demote_their_own_account(): void
    {
        $admin = User::factory()->create(['rol' => 'administrador']);

        $this->actingAs($admin)
            ->from('/usuarios')
            ->put("/usuarios/{$admin->id}/rol", ['rol' => 'cajero'])
            ->assertRedirect('/usuarios')
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'rol' => 'administrador',
        ]);
    }
}
