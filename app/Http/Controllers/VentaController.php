<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class VentaController extends Controller
{
    public function pos(Request $request): Response
    {
        return Inertia::render('Ferreteria/Pos', [
            'productos' => Producto::query()
                ->with('categoria:id,nombre')
                ->orderBy('nombre')
                ->get(),
            'categorias' => Categoria::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'clientes' => Cliente::query()
                ->orderBy('nombre')
                ->get(['id', 'nombre']),
            'clienteCreadoId' => $request->session()->get('cliente_creado_id'),
            'ventaPdfUrl' => $request->session()->get('venta_pdf_url'),
        ]);
    }

    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'metodo_pago' => ['nullable', Rule::in(['Efectivo', 'Tarjeta', 'Transferencia'])],
        ]);

        $query = Venta::query()
            ->with([
                'cliente:id,nombre',
                'user:id,name',
                'detalles' => fn ($details) => $details
                    ->select('id', 'venta_id', 'producto_id', 'cantidad', 'precio_unitario', 'subtotal')
                    ->with('producto:id,nombre,codigo_sku'),
            ]);

        $this->applyHistoryFilters($query, $filters);

        $totals = (clone $query)
            ->reorder()
            ->selectRaw(
                'COALESCE(SUM(total), 0) as total_ventas, COALESCE(SUM(CASE WHEN metodo_pago = ? THEN total ELSE 0 END), 0) as total_efectivo',
                ['Efectivo'],
            )
            ->first();

        $ventas = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Ferreteria/HistorialVentas', [
            'ventas' => $ventas->getCollection()
                ->map(fn (Venta $venta): array => [
                    'id' => $venta->id,
                    'codigo_venta' => $venta->codigo_venta,
                    'cliente' => $venta->cliente?->nombre ?? 'Cliente General',
                    'usuario' => $venta->user?->name ?? 'Usuario no disponible',
                    'metodo_pago' => $venta->metodo_pago,
                    'total' => $venta->total,
                    'subtotal' => $venta->subtotal,
                    'descuento' => $venta->descuento ?? '0.00',
                    'moneda' => $venta->moneda,
                    'fecha' => $venta->fecha_venta?->format('d/m/Y H:i') ?? '—',
                    'productos' => $venta->detalles
                        ->map(fn ($detalle): array => [
                            'id' => $detalle->id,
                            'nombre' => $detalle->producto?->nombre ?? 'Producto no disponible',
                            'codigo_sku' => $detalle->producto?->codigo_sku,
                            'cantidad' => $detalle->cantidad,
                            'precio_unitario' => $detalle->precio_unitario,
                            'subtotal' => $detalle->subtotal,
                        ])
                        ->values(),
                ])
                ->values(),
            'totales' => [
                'ventas' => $totals?->total_ventas ?? 0,
                'efectivo' => $totals?->total_efectivo ?? 0,
            ],
            'filtros' => [
                'q' => $filters['q'] ?? '',
                'desde' => $filters['desde'] ?? '',
                'hasta' => $filters['hasta'] ?? '',
                'metodo_pago' => $filters['metodo_pago'] ?? '',
            ],
            'pagination' => [
                'currentPage' => $ventas->currentPage(),
                'lastPage' => $ventas->lastPage(),
                'previousPageUrl' => $ventas->previousPageUrl(),
                'nextPageUrl' => $ventas->nextPageUrl(),
            ],
        ]);
    }

    /**
     * @param  array{q?: string|null, desde?: string|null, hasta?: string|null, metodo_pago?: string|null}  $filters
     */
    private function applyHistoryFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['q'])) {
            $search = trim($filters['q']);

            $query->where(function (Builder $query) use ($search): void {
                $query->whereHas('cliente', fn (Builder $clientQuery) => $clientQuery
                    ->where('nombre', 'like', '%'.$search.'%'));

                $query->orWhere('ventas.codigo_venta', 'like', '%'.$search.'%');

                if (ctype_digit($search)) {
                    $query->orWhere('ventas.id', (int) $search);
                }
            });
        }

        if (! empty($filters['desde'])) {
            $query->whereDate('fecha_venta', '>=', $filters['desde']);
        }

        if (! empty($filters['hasta'])) {
            $query->whereDate('fecha_venta', '<=', $filters['hasta']);
        }

        if (! empty($filters['metodo_pago'])) {
            $query->where('metodo_pago', $filters['metodo_pago']);
        }
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'metodo_pago' => ['required', Rule::in(['Efectivo', 'Tarjeta', 'Transferencia'])],
            'items' => ['required', 'array', 'min:1'],
            'items.*.producto_id' => ['required', 'integer', 'distinct', 'exists:productos,id'],
            'items.*.cantidad' => ['required', 'numeric', 'gt:0', 'decimal:0,3', 'max:9999999.999'],
        ]);

        $userId = $request->user()?->getKey();

        if ($userId === null) {
            throw ValidationException::withMessages([
                'venta' => 'Debe existir un usuario registrado para asociar la venta.',
            ]);
        }

        $sale = DB::transaction(function () use ($validated, $userId): Venta {
            $items = collect($validated['items']);
            $productIds = $items
                ->pluck('producto_id')
                ->map(fn (int $productId): int => $productId)
                ->sort()
                ->values();

            $products = Producto::query()
                ->whereKey($productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== $items->count()) {
                throw ValidationException::withMessages([
                    'items' => 'Uno o más productos ya no están disponibles.',
                ]);
            }

            $maximumAmountInCents = 999_999_999_999;
            $subtotalInCents = 0;
            $details = [];

            foreach ($items as $item) {
                $product = $products->get($item['producto_id']);
                $quantityMilli = $this->toScaledInteger((string) $item['cantidad'], 3);
                $stockMilli = $this->toScaledInteger((string) $product->stock, 3);

                if ($stockMilli < $quantityMilli) {
                    throw ValidationException::withMessages([
                        'items' => "Stock insuficiente para {$product->nombre}.",
                    ]);
                }

                $unitPriceInCents = $this->toScaledInteger((string) $product->precio_venta, 2);
                $remainingAmountInCents = $maximumAmountInCents - $subtotalInCents;

                if ($unitPriceInCents > 0
                    && $quantityMilli > intdiv($remainingAmountInCents * 1000, $unitPriceInCents)) {
                    throw ValidationException::withMessages([
                        'items' => 'El total de la venta supera el máximo permitido.',
                    ]);
                }

                $lineTotalInCents = intdiv(($unitPriceInCents * $quantityMilli) + 500, 1000);
                $subtotalInCents += $lineTotalInCents;

                $details[] = [
                    'product' => $product,
                    'quantity_milli' => $quantityMilli,
                    'unit_price' => number_format($unitPriceInCents / 100, 2, '.', ''),
                    'subtotal' => number_format($lineTotalInCents / 100, 2, '.', ''),
                ];
            }

            $discountInCents = $subtotalInCents > Venta::MONTO_MINIMO_DESCUENTO * 100
                ? (int) round(
                    $subtotalInCents * Venta::PORCENTAJE_DESCUENTO,
                    0,
                    PHP_ROUND_HALF_UP,
                )
                : 0;
            $totalInCents = $subtotalInCents - $discountInCents;

            $sale = Venta::query()->create([
                'cliente_id' => $validated['cliente_id'] ?? null,
                'user_id' => $userId,
                'subtotal' => number_format($subtotalInCents / 100, 2, '.', ''),
                'descuento' => number_format($discountInCents / 100, 2, '.', ''),
                'total' => number_format($totalInCents / 100, 2, '.', ''),
                'moneda' => 'COP',
                'metodo_pago' => $validated['metodo_pago'],
                'estado' => 'completada',
                'fecha_venta' => now(),
            ]);

            foreach ($details as $detail) {
                /** @var Producto $product */
                $product = $detail['product'];
                $stockMilli = $this->toScaledInteger((string) $product->stock, 3);
                $product->stock = $this->formatScaledInteger($stockMilli - $detail['quantity_milli'], 3);
                $product->save();

                $sale->detalles()->create([
                    'producto_id' => $product->id,
                    'cantidad' => $this->formatScaledInteger($detail['quantity_milli'], 3),
                    'precio_unitario' => $detail['unit_price'],
                    'subtotal' => $detail['subtotal'],
                ]);
            }

            return $sale;
        });

        return redirect()
            ->route('pos.index')
            ->with('success', 'Venta registrada correctamente.')
            ->with('venta_pdf_url', route('ventas.factura', $sale));
    }

    public function downloadInvoice(Venta $venta): HttpResponse
    {
        $venta->load([
            'cliente:id,nombre,cedula_nit,telefono,direccion',
            'user:id,name',
            'detalles' => fn ($query) => $query->with('producto:id,nombre,codigo_sku'),
        ]);

        return Pdf::loadView('pdf.factura', ['venta' => $venta])
            ->setPaper('a4')
            ->download('recibo-'.$venta->codigo_venta.'.pdf');
    }

    private function toScaledInteger(string $value, int $scale): int
    {
        $value = trim($value);

        if (! preg_match('/^(\\d+)(?:\\.(\\d+))?$/', $value, $matches)) {
            throw ValidationException::withMessages([
                'items' => 'Una cantidad o un precio tiene un formato inválido.',
            ]);
        }

        $fraction = substr(str_pad($matches[2] ?? '', $scale, '0'), 0, $scale);

        return ((int) $matches[1] * (10 ** $scale)) + (int) $fraction;
    }

    private function formatScaledInteger(int $value, int $scale): string
    {
        $base = 10 ** $scale;
        $whole = intdiv($value, $base);
        $fraction = str_pad((string) ($value % $base), $scale, '0', STR_PAD_LEFT);

        return $whole.'.'.$fraction;
    }
}
