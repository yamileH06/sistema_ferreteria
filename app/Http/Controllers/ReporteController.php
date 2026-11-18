<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReporteController extends Controller
{
    /**
     * Display sales reports, key metrics, and best/worst selling products.
     */
    public function index(Request $request): Response
    {
        $data = $this->buildReportData($request);

        return Inertia::render('Ferreteria/Reportes', [
            'metricas' => $data['metricas'],
            'metodos_pago' => $data['metodos_pago'],
            'ventas_por_dia' => $data['ventas_por_dia'],
            'mas_vendidos' => $data['mas_vendidos'],
            'menos_vendidos' => $data['menos_vendidos'],
            'filtros' => $data['filtros'],
        ]);
    }

    /**
     * Generate and download a formal PDF sales report.
     */
    public function exportPdf(Request $request): HttpResponse
    {
        $data = $this->buildReportData($request);

        $periodo = $data['filtros']['periodo'];
        $desde = $data['filtros']['desde'];
        $hasta = $data['filtros']['hasta'];

        $periodoLabel = match ($periodo) {
            'hoy' => 'Hoy ('.Carbon::now()->format('d/m/Y').')',
            'ayer' => 'Ayer ('.Carbon::now()->subDay()->format('d/m/Y').')',
            '7dias' => 'Últimos 7 días ('.Carbon::now()->subDays(6)->format('d/m/Y').' al '.Carbon::now()->format('d/m/Y').')',
            'este_mes' => 'Este mes ('.Carbon::now()->startOfMonth()->format('d/m/Y').' al '.Carbon::now()->format('d/m/Y').')',
            'mes_anterior' => 'Mes anterior ('.Carbon::now()->subMonth()->startOfMonth()->format('d/m/Y').' al '.Carbon::now()->subMonth()->endOfMonth()->format('d/m/Y').')',
            'todo' => 'Histórico total (Todas las fechas registradas)',
            default => (! empty($desde) && ! empty($hasta))
                ? "Del {$desde} al {$hasta}"
                : (! empty($desde)
                    ? "Desde {$desde}"
                    : (! empty($hasta)
                        ? "Hasta {$hasta}"
                        : 'Histórico completo')),
        };

        $viewData = [
            'metricas' => $data['metricas'],
            'metodos_pago' => $data['metodos_pago'],
            'ventas_por_dia' => $data['ventas_por_dia'],
            'mas_vendidos' => $data['mas_vendidos'],
            'menos_vendidos' => $data['menos_vendidos'],
            'productos_vendidos' => $data['productos_vendidos'],
            'filtros' => $data['filtros'],
            'periodo_label' => $periodoLabel,
            'usuario' => $request->user()?->name ?? 'Usuario del Sistema',
            'fecha_emision' => Carbon::now()->format('d/m/Y h:i A'),
        ];

        $filename = 'reporte-ventas-'.now()->format('Ymd_His').'.pdf';

        return Pdf::loadView('pdf.reporte-ventas', $viewData)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildReportData(Request $request): array
    {
        $validated = $request->validate([
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
            'periodo' => [
                'nullable',
                'string',
                Rule::in(['hoy', 'ayer', '7dias', 'este_mes', 'mes_anterior', 'todo', 'personalizado']),
            ],
        ]);

        $periodo = $validated['periodo'] ?? null;
        $desde = $validated['desde'] ?? null;
        $hasta = $validated['hasta'] ?? null;

        // If no explicit dates or periodo is provided, default to 'este_mes'
        if ($periodo === null && $desde === null && $hasta === null) {
            $periodo = 'este_mes';
        }

        // Resolve dates based on preset periodo if selected
        if ($periodo !== null && $periodo !== 'personalizado') {
            switch ($periodo) {
                case 'hoy':
                    $desde = Carbon::now()->toDateString();
                    $hasta = Carbon::now()->toDateString();
                    break;
                case 'ayer':
                    $desde = Carbon::now()->subDay()->toDateString();
                    $hasta = Carbon::now()->subDay()->toDateString();
                    break;
                case '7dias':
                    $desde = Carbon::now()->subDays(6)->toDateString();
                    $hasta = Carbon::now()->toDateString();
                    break;
                case 'este_mes':
                    $desde = Carbon::now()->startOfMonth()->toDateString();
                    $hasta = Carbon::now()->toDateString();
                    break;
                case 'mes_anterior':
                    $desde = Carbon::now()->subMonth()->startOfMonth()->toDateString();
                    $hasta = Carbon::now()->subMonth()->endOfMonth()->toDateString();
                    break;
                case 'todo':
                    $desde = null;
                    $hasta = null;
                    break;
            }
        } else {
            $periodo = 'personalizado';
        }

        $ventasQuery = Venta::query();

        if (! empty($desde)) {
            $ventasQuery->whereDate('fecha_venta', '>=', $desde);
        }

        if (! empty($hasta)) {
            $ventasQuery->whereDate('fecha_venta', '<=', $hasta);
        }

        $totalIngresos = (float) ((clone $ventasQuery)->sum('total') ?? 0);
        $totalTransacciones = (int) (clone $ventasQuery)->count();
        $totalDescuentos = (float) ((clone $ventasQuery)->sum('descuento') ?? 0);

        $periodSaleIds = (clone $ventasQuery)->pluck('id');

        $totalProductosVendidos = (float) (DB::table('detalle_ventas')
            ->whereIn('venta_id', $periodSaleIds)
            ->sum('cantidad') ?? 0);

        $ticketPromedio = $totalTransacciones > 0
            ? round($totalIngresos / $totalTransacciones, 2)
            : 0;

        // Breakdown by payment method
        $metodosPago = (clone $ventasQuery)
            ->select('metodo_pago', DB::raw('COUNT(*) as total_transacciones'), DB::raw('SUM(total) as total_monto'))
            ->groupBy('metodo_pago')
            ->get()
            ->map(fn ($item): array => [
                'metodo' => $item->metodo_pago,
                'transacciones' => (int) $item->total_transacciones,
                'monto' => (float) $item->total_monto,
                'porcentaje' => $totalIngresos > 0
                    ? round(((float) $item->total_monto / $totalIngresos) * 100, 1)
                    : 0,
            ])
            ->values();

        // Daily breakdown in the period
        $ventasPorDia = (clone $ventasQuery)
            ->select(
                DB::raw('DATE(fecha_venta) as fecha'),
                DB::raw('COUNT(*) as transacciones'),
                DB::raw('SUM(total) as total_monto')
            )
            ->groupBy(DB::raw('DATE(fecha_venta)'))
            ->orderBy(DB::raw('DATE(fecha_venta)'), 'asc')
            ->get()
            ->map(fn ($item): array => [
                'fecha' => (string) $item->fecha,
                'transacciones' => (int) $item->transacciones,
                'total' => (float) $item->total_monto,
            ])
            ->values();

        // Top best selling products
        $masVendidos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereIn('detalle_ventas.venta_id', $periodSaleIds)
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.codigo_sku',
                'productos.stock',
                'productos.precio_venta',
                'productos.imagen',
                'categorias.nombre as categoria_nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_ventas.subtotal) as total_recaudado'),
                DB::raw('COUNT(DISTINCT detalle_ventas.venta_id) as total_pedidos')
            )
            ->groupBy(
                'productos.id',
                'productos.nombre',
                'productos.codigo_sku',
                'productos.stock',
                'productos.precio_venta',
                'productos.imagen',
                'categorias.nombre'
            )
            ->orderByDesc('total_vendido')
            ->limit(10)
            ->get()
            ->map(fn ($p): array => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo_sku' => $p->codigo_sku,
                'categoria' => $p->categoria_nombre ?? 'General',
                'stock' => (float) $p->stock,
                'precio_venta' => (float) $p->precio_venta,
                'imagen' => $p->imagen,
                'imagen_url' => $p->imagen
                    ? (str_starts_with($p->imagen, 'http') ? $p->imagen : '/storage/'.ltrim($p->imagen, '/'))
                    : null,
                'total_vendido' => (float) $p->total_vendido,
                'total_recaudado' => (float) $p->total_recaudado,
                'total_pedidos' => (int) $p->total_pedidos,
            ])
            ->values();

        // All sold products in period for PDF
        $productosVendidos = DB::table('detalle_ventas')
            ->join('productos', 'detalle_ventas.producto_id', '=', 'productos.id')
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->whereIn('detalle_ventas.venta_id', $periodSaleIds)
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.codigo_sku',
                'productos.stock',
                'productos.precio_venta',
                'categorias.nombre as categoria_nombre',
                DB::raw('SUM(detalle_ventas.cantidad) as total_vendido'),
                DB::raw('SUM(detalle_ventas.subtotal) as total_recaudado'),
                DB::raw('COUNT(DISTINCT detalle_ventas.venta_id) as total_pedidos')
            )
            ->groupBy(
                'productos.id',
                'productos.nombre',
                'productos.codigo_sku',
                'productos.stock',
                'productos.precio_venta',
                'categorias.nombre'
            )
            ->orderByDesc('total_recaudado')
            ->get()
            ->map(fn ($p): array => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo_sku' => $p->codigo_sku,
                'categoria' => $p->categoria_nombre ?? 'General',
                'stock' => (float) $p->stock,
                'precio_venta' => (float) $p->precio_venta,
                'total_vendido' => (float) $p->total_vendido,
                'total_recaudado' => (float) $p->total_recaudado,
                'total_pedidos' => (int) $p->total_pedidos,
            ])
            ->values();

        // Least selling products (low rotation / stagnant inventory)
        $salesAgg = DB::table('detalle_ventas')
            ->whereIn('venta_id', $periodSaleIds)
            ->select(
                'producto_id',
                DB::raw('SUM(cantidad) as total_vendido'),
                DB::raw('SUM(subtotal) as total_recaudado'),
                DB::raw('COUNT(DISTINCT venta_id) as total_pedidos')
            )
            ->groupBy('producto_id');

        $menosVendidos = DB::table('productos')
            ->leftJoinSub($salesAgg, 'resumen_ventas', function ($join): void {
                $join->on('productos.id', '=', 'resumen_ventas.producto_id');
            })
            ->leftJoin('categorias', 'productos.categoria_id', '=', 'categorias.id')
            ->select(
                'productos.id',
                'productos.nombre',
                'productos.codigo_sku',
                'productos.stock',
                'productos.precio_venta',
                'productos.imagen',
                'categorias.nombre as categoria_nombre',
                DB::raw('COALESCE(resumen_ventas.total_vendido, 0) as total_vendido'),
                DB::raw('COALESCE(resumen_ventas.total_recaudado, 0) as total_recaudado'),
                DB::raw('COALESCE(resumen_ventas.total_pedidos, 0) as total_pedidos')
            )
            ->orderBy('total_vendido', 'asc')
            ->orderByDesc('productos.stock')
            ->limit(10)
            ->get()
            ->map(fn ($p): array => [
                'id' => $p->id,
                'nombre' => $p->nombre,
                'codigo_sku' => $p->codigo_sku,
                'categoria' => $p->categoria_nombre ?? 'General',
                'stock' => (float) $p->stock,
                'precio_venta' => (float) $p->precio_venta,
                'imagen' => $p->imagen,
                'imagen_url' => $p->imagen
                    ? (str_starts_with($p->imagen, 'http') ? $p->imagen : '/storage/'.ltrim($p->imagen, '/'))
                    : null,
                'total_vendido' => (float) $p->total_vendido,
                'total_recaudado' => (float) $p->total_recaudado,
                'total_pedidos' => (int) $p->total_pedidos,
            ])
            ->values();

        return [
            'metricas' => [
                'total_ingresos' => $totalIngresos,
                'total_transacciones' => $totalTransacciones,
                'total_productos_vendidos' => $totalProductosVendidos,
                'ticket_promedio' => $ticketPromedio,
                'total_descuentos' => $totalDescuentos,
            ],
            'metodos_pago' => $metodosPago,
            'ventas_por_dia' => $ventasPorDia,
            'mas_vendidos' => $masVendidos,
            'menos_vendidos' => $menosVendidos,
            'productos_vendidos' => $productosVendidos,
            'filtros' => [
                'desde' => $desde ?? '',
                'hasta' => $hasta ?? '',
                'periodo' => $periodo,
            ],
        ];
    }
}
