<?php

namespace Tests\Feature;

use App\Models\Cliente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PosQuickClientTest extends TestCase
{
    use RefreshDatabase;

    public function test_quick_client_creation_redirects_to_pos_and_exposes_the_new_client_id(): void
    {
        $response = $this->from('/pos')->post('/pos/clientes', [
            'nombre' => 'María Fernanda Rosero',
            'cedula_nit' => '1087654321',
            'telefono' => '3123456789',
        ]);

        $client = Cliente::query()->where('cedula_nit', '1087654321')->firstOrFail();

        $response->assertRedirect(route('pos.index'))
            ->assertSessionHas('cliente_creado_id', $client->id)
            ->assertSessionHas('success', 'Cliente registrado correctamente.');

        $this->get('/pos')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Ferreteria/Pos')
                ->where('clienteCreadoId', $client->id)
                ->where('clientes.0.id', $client->id)
                ->where('clientes.0.nombre', 'María Fernanda Rosero'));
    }

    public function test_quick_client_creation_rejects_missing_required_document(): void
    {
        $this->from('/pos')
            ->post('/pos/clientes', [
                'nombre' => 'Cliente sin documento',
                'telefono' => '3123456789',
            ])
            ->assertRedirect('/pos')
            ->assertSessionHasErrors('cedula_nit');

        $this->assertDatabaseCount('clientes', 0);
    }

    public function test_quick_client_creation_rejects_duplicate_document(): void
    {
        Cliente::query()->create([
            'nombre' => 'Cliente existente',
            'cedula_nit' => '1087654321',
        ]);

        $this->from('/pos')
            ->post('/pos/clientes', [
                'nombre' => 'Otro cliente',
                'cedula_nit' => '1087654321',
            ])
            ->assertRedirect('/pos')
            ->assertSessionHasErrors('cedula_nit');

        $this->assertDatabaseCount('clientes', 1);
    }
}
