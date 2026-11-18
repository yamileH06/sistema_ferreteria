<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Proveedor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(): Response
    {
        $productos = Producto::with(['categoria', 'proveedor'])->get();
        $categorias = Categoria::all();
        $proveedores = Proveedor::all();

        return Inertia::render('Ferreteria/Inventario', [
            'productos' => $productos,
            'categorias' => $categorias,
            'proveedores' => $proveedores,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_sku' => ['required', 'string', 'max:255', 'unique:productos,codigo_sku'],
            'categoria_id' => ['required', 'exists:categorias,id'],
            'proveedor_id' => ['nullable', 'exists:proveedors,id'],
            'stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:9999999.999'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable'],
            'imagen_url' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        } elseif (! empty($validated['imagen_url'])) {
            $validated['imagen'] = trim($validated['imagen_url']);
        } elseif (is_string($request->input('imagen')) && str_starts_with($request->input('imagen'), 'http')) {
            $validated['imagen'] = trim($request->input('imagen'));
        } else {
            $validated['imagen'] = null;
        }

        unset($validated['imagen_url']);

        Producto::create($validated);

        return redirect()->back()->with('success', 'Producto registrado exitosamente en el inventario.');
    }

    public function storeInCategory(Request $request, Categoria $categoria): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_sku' => ['required', 'string', 'max:255', 'unique:productos,codigo_sku'],
            'stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:9999999.999'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable'],
            'imagen_url' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        } elseif (! empty($validated['imagen_url'])) {
            $validated['imagen'] = trim($validated['imagen_url']);
        } elseif (is_string($request->input('imagen')) && str_starts_with($request->input('imagen'), 'http')) {
            $validated['imagen'] = trim($request->input('imagen'));
        } else {
            $validated['imagen'] = null;
        }

        unset($validated['imagen_url']);

        $categoria->productos()->create($validated);

        return back()->with('success', 'Producto agregado a la categoría correctamente.');
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'codigo_sku' => [
                'required',
                'string',
                'max:255',
                Rule::unique('productos', 'codigo_sku')->ignore($producto->id),
            ],
            'proveedor_id' => ['nullable', 'exists:proveedors,id'],
            'stock' => ['required', 'numeric', 'decimal:0,3', 'min:0', 'max:9999999.999'],
            'precio_compra' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0'],
            'imagen' => ['nullable'],
            'imagen_url' => ['nullable', 'string', 'max:2000'],
            'quitar_imagen' => ['nullable'],
        ]);

        // La categoría del producto solo se asigna en la creación (vía URL del proveedor).
        // No se permite cambiarla desde el formulario de edición.
        unset($validated['categoria_id']);

        if ($request->boolean('quitar_imagen') || $request->input('quitar_imagen') === '1') {
            if ($producto->imagen && ! str_starts_with($producto->imagen, 'http') && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = null;
        } elseif ($request->hasFile('imagen')) {
            $request->validate([
                'imagen' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);
            if ($producto->imagen && ! str_starts_with($producto->imagen, 'http') && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = $request->file('imagen')->store('productos', 'public');
        } elseif (! empty($validated['imagen_url'])) {
            if ($producto->imagen && ! str_starts_with($producto->imagen, 'http') && Storage::disk('public')->exists($producto->imagen)) {
                Storage::disk('public')->delete($producto->imagen);
            }
            $validated['imagen'] = trim($validated['imagen_url']);
        } elseif (is_string($request->input('imagen')) && str_starts_with($request->input('imagen'), 'http')) {
            $validated['imagen'] = trim($request->input('imagen'));
        } else {
            unset($validated['imagen']);
        }

        unset($validated['imagen_url'], $validated['quitar_imagen']);

        $producto->update($validated);

        return back()->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        if ($producto->detallesVenta()->exists()) {
            return back()->withErrors([
                'producto' => 'No se puede eliminar este producto porque está asociado a ventas registradas.',
            ]);
        }

        if ($producto->imagen && Storage::disk('public')->exists($producto->imagen)) {
            Storage::disk('public')->delete($producto->imagen);
        }

        $producto->delete();

        return back()->with('success', 'Producto eliminado correctamente.');
    }
}
