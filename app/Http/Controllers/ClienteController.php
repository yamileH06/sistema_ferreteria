<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ClienteController extends Controller
{
    public function storeFromPos(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'cedula_nit' => ['required', 'string', 'max:255', Rule::unique('clientes', 'cedula_nit')],
            'telefono' => ['nullable', 'string', 'max:255'],
        ]);

        $cliente = Cliente::query()->create($validated);

        return to_route('pos.index')
            ->with('cliente_creado_id', $cliente->id)
            ->with('success', 'Cliente registrado correctamente.');
    }

    public function index(): Response
    {
        return Inertia::render('Ferreteria/Consulta', [
            'title' => 'Clientes',
            'description' => 'Clientes registrados en el sistema.',
            'columns' => ['Nombre', 'Cédula / NIT', 'Teléfono'],
            'rows' => Cliente::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'cedula_nit', 'telefono'])
                ->map(fn (Cliente $cliente): array => [
                    'id' => $cliente->id,
                    'values' => [
                        $cliente->nombre,
                        $cliente->cedula_nit,
                        $cliente->telefono ?? '—',
                    ],
                ])
                ->values(),
        ]);
    }
}
