<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(): Response
    {
        $categorias = Categoria::query()
            ->withCount('productos')
            ->withSum('productos as stock_disponible', 'stock')
            ->with(['productos' => fn ($query) => $query
                ->select('id', 'categoria_id', 'nombre', 'codigo_sku', 'stock', 'precio_compra', 'precio_venta')
                ->orderBy('nombre')
                ->orderBy('codigo_sku')])
            ->orderBy('nombre')
            ->get()
            ->map(fn (Categoria $categoria): array => [
                'id' => $categoria->id,
                'nombre' => $categoria->nombre,
                'productos_count' => $categoria->productos_count,
                'stock_disponible' => (int) ($categoria->stock_disponible ?? 0),
                'productos' => $categoria->productos
                    ->map(fn (Producto $producto): array => [
                        'id' => $producto->id,
                        'nombre' => $producto->nombre,
                        'codigo_sku' => $producto->codigo_sku,
                        'stock' => $producto->stock,
                        'precio_compra' => $producto->precio_compra,
                        'precio_venta' => $producto->precio_venta,
                    ])
                    ->values(),
            ])
            ->values();

        return Inertia::render('Ferreteria/Categorias', [
            'categorias' => $categorias,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:categorias,nombre'],
        ]);

        Categoria::query()->create($validated);

        return to_route('categorias.index');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:categorias,nombre,'.$categoria->id],
        ]);

        $categoria->update($validated);

        return to_route('categorias.index');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        $deleted = DB::transaction(function () use ($categoria): bool {
            $lockedCategory = Categoria::query()
                ->whereKey($categoria->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedCategory->productos()->exists()) {
                return false;
            }

            $lockedCategory->delete();

            return true;
        });

        if (! $deleted) {
            return to_route('categorias.index')
                ->withErrors(['categoria' => 'No se puede eliminar esta categoría porque tiene productos asociados.']);
        }

        return to_route('categorias.index');
    }
}
