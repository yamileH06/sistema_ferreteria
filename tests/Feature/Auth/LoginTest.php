<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders_the_authentication_form(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Auth/Login'));
    }

    public function test_valid_administrator_credentials_authenticate_and_redirect_to_inventory(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
            'rol' => 'administrador',
        ]);

        $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ])
            ->assertRedirect(route('inventario.index'))
            ->assertSessionHasNoErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_credentials_return_to_login_with_a_generic_error(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => 'secure-password',
        ]);

        $this->from('/login')
            ->post('/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'El usuario o la contraseña no son correctos.',
            ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_is_redirected_away_from_login(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/login')
            ->assertRedirect(route('inventario.index'));
    }

    public function test_cajero_login_redirects_to_the_point_of_sale(): void
    {
        User::factory()->create([
            'email' => 'cajero@example.com',
            'password' => 'secure-password',
            'rol' => 'cajero',
        ]);

        $this->post('/login', [
            'email' => 'cajero@example.com',
            'password' => 'secure-password',
        ])->assertRedirect(route('pos.index'));
    }

    public function test_bodeguero_login_redirects_to_the_inventory(): void
    {
        User::factory()->create([
            'email' => 'bodeguero@example.com',
            'password' => 'secure-password',
            'rol' => 'bodeguero',
        ]);

        $this->post('/login', [
            'email' => 'bodeguero@example.com',
            'password' => 'secure-password',
        ])->assertRedirect(route('inventario.index'));
    }
}
