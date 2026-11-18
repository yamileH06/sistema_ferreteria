<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Venta;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Muestra la vista principal del panel de control (Dashboard).
     */
    public function index(Request $request): Response
    {
        $today = now()->today();

        $ventasHoy = (float) Venta::query()
            ->whereDate('fecha_venta', $today)
            ->sum('total');

        $ventasHoyCount = Venta::query()
            ->whereDate('fecha_venta', $today)
            ->count();

        $totalProductos = Producto::query()->count();
        $totalStock = (float) Producto::query()->sum('stock');
        $alertasStockBajo = Producto::query()->where('stock', '<=', 10)->count();

        $totalClientes = Cliente::query()->count();
        $totalProveedores = Proveedor::query()->count();

        $ultimasVentas = Venta::query()
            ->with('cliente:id,nombre')
            ->latest('fecha_venta')
            ->take(5)
            ->get()
            ->map(fn (Venta $venta): array => [
                'id' => $venta->id,
                'codigo_venta' => $venta->codigo_venta,
                'cliente' => $venta->cliente?->nombre ?? 'Cliente General',
                'metodo_pago' => $venta->metodo_pago ?? 'Efectivo',
                'total' => (float) $venta->total,
                'fecha' => $venta->fecha_venta?->format('d/m/Y H:i') ?? '—',
                'pdf_url' => route('ventas.factura', $venta),
            ]);

        $productosCriticos = Producto::query()
            ->with([
                'categoria:id,nombre',
                'proveedor:id,nombre',
            ])
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get()
            ->map(fn (Producto $producto): array => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'codigo_sku' => $producto->codigo_sku,
                'stock' => (float) $producto->stock,
                'categoria' => $producto->categoria?->nombre ?? 'Sin categoría',
                'proveedor' => $producto->proveedor?->nombre ?? 'Sin proveedor',
                'precio_venta' => (float) $producto->precio_venta,
            ]);

        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
            'stats' => [
                'ventas_hoy' => $ventasHoy,
                'ventas_hoy_count' => $ventasHoyCount,
                'total_productos' => $totalProductos,
                'total_stock' => $totalStock,
                'alertas_stock_bajo' => $alertasStockBajo,
                'total_clientes' => $totalClientes,
                'total_proveedores' => $totalProveedores,
            ],
            'ultimasVentas' => $ultimasVentas,
            'productosCriticos' => $productosCriticos,
        ]);
    }
}
