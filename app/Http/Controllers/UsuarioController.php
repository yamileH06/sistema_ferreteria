<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UsuarioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Ferreteria/Usuarios', [
            'usuarios' => User::query()
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'rol']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'rol' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        unset($validated['password_confirmation']);

        User::query()->create($validated);

        return to_route('usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'rol' => ['required', 'string', Rule::in(User::ROLES)],
        ]);

        if ($user->is($request->user())) {
            return back()->with('error', 'No puedes cambiar el rol de tu propia cuenta.');
        }

        if ($user->rol === 'administrador'
            && $validated['rol'] !== 'administrador'
            && User::query()->where('rol', 'administrador')->count() <= 1) {
            return back()->with('error', 'Debe permanecer al menos un usuario administrador.');
        }

        $user->update(['rol' => $validated['rol']]);

        return back()->with('success', 'Rol actualizado correctamente.');
    }
}
