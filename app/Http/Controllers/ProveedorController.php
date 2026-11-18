<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Reabastecimiento;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProveedorController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Ferreteria/Proveedores', [
            'proveedores' => Proveedor::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'nit', 'telefono', 'correo', 'direccion']),
            'productos' => Producto::query()
                ->with('categoria:id,nombre')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'codigo_sku', 'stock', 'precio_compra', 'precio_venta', 'categoria_id', 'proveedor_id']),
        ]);
    }

    public function reabastecer(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:9999999.999'],
            'items.*.costo_unitario' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'notas' => ['nullable', 'string', 'max:1000'],
        ], [
            'items.required' => 'Debes seleccionar al menos un producto para reabastecer.',
            'items.*.producto_id.required' => 'El producto es obligatorio.',
            'items.*.producto_id.distinct' => 'No puedes repetir el mismo producto en el reabastecimiento.',
            'items.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'items.*.cantidad.required' => 'La cantidad recibida es obligatoria.',
            'items.*.cantidad.gt' => 'La cantidad recibida debe ser mayor a 0.',
            'items.*.cantidad.max' => 'La cantidad recibida supera el límite permitido.',
            'items.*.costo_unitario.min' => 'El costo unitario no puede ser negativo.',
        ]);

        $userId = $request->user()?->id;

        $summary = DB::transaction(function () use ($proveedor, $validated, $userId): array {
            $items = collect($validated['items']);
            $productIds = $items->pluck('producto_id')->sort()->values();

            $products = Producto::query()
                ->whereKey($productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $codigoLote = 'REA-'.now()->format('YmdHis').'-'.str_pad((string) $proveedor->id, 4, '0', STR_PAD_LEFT);
            $totalCantidadAcumulada = '0';
            $productosAfectados = 0;

            foreach ($items as $item) {
                /** @var Producto $product */
                $product = $products->get($item['producto_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => 'Uno o más productos ya no se encuentran disponibles.',
                    ]);
                }

                $cantidadStr = (string) $item['cantidad'];
                $costoUnitario = isset($item['costo_unitario']) && $item['costo_unitario'] !== '' && $item['costo_unitario'] !== null
                    ? (float) $item['costo_unitario']
                    : null;

                $costoTotal = $costoUnitario !== null
                    ? round($costoUnitario * (float) $cantidadStr, 2)
                    : null;

                // Sumar al stock actual (NUNCA sobrescribir, siempre sumar stock + cantidad)
                $product->stock = bcadd((string) $product->stock, $cantidadStr, 3);

                if ($costoUnitario !== null && $costoUnitario > 0) {
                    $product->precio_compra = number_format($costoUnitario, 2, '.', '');
                }

                if ($product->proveedor_id === null) {
                    $product->proveedor_id = $proveedor->id;
                }

                $product->save();

                Reabastecimiento::query()->create([
                    'proveedor_id' => $proveedor->id,
                    'producto_id' => $product->id,
                    'user_id' => $userId,
                    'cantidad' => $cantidadStr,
                    'costo_unitario' => $costoUnitario,
                    'costo_total' => $costoTotal,
                    'codigo_lote' => $codigoLote,
                    'fecha_recepcion' => now(),
                    'notas' => $validated['notas'] ?? null,
                ]);

                $totalCantidadAcumulada = bcadd($totalCantidadAcumulada, $cantidadStr, 3);
                $productosAfectados++;
            }

            return [
                'total_cantidad' => $totalCantidadAcumulada,
                'productos_count' => $productosAfectados,
                'codigo_lote' => $codigoLote,
            ];
        });

        $unidadesFormateadas = (float) $summary['total_cantidad'] == (int) $summary['total_cantidad']
            ? number_format((float) $summary['total_cantidad'], 0, ',', '.')
            : number_format((float) $summary['total_cantidad'], 3, ',', '.');

        return back()->with(
            'success',
            "Reabastecimiento completado: se sumaron {$unidadesFormateadas} unidades al inventario a través de {$proveedor->nombre}.",
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ]);

        Proveedor::query()->create($validated);

        return back()->with('success', 'Proveedor registrado correctamente.');
    }

    public function update(Request $request, Proveedor $proveedor): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
            'nit' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:255'],
            'correo' => ['nullable', 'email', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:2000'],
        ]);

        $proveedor->update($validated);

        return back()->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        $deleted = DB::transaction(function () use ($proveedor): bool {
            $lockedProveedor = Proveedor::query()
                ->whereKey($proveedor->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedProveedor->productos()->exists() || $lockedProveedor->reabastecimientos()->exists()) {
                return false;
            }

            $lockedProveedor->delete();

            return true;
        });

        if (! $deleted) {
            return back()->withErrors([
                'proveedor' => 'No se puede eliminar este proveedor porque tiene productos o compras registradas.',
            ]);
        }

        return back()->with('success', 'Proveedor eliminado correctamente.');
    }
}
