<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('home');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('login.store');

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::middleware('role:administrador,bodeguero')->group(function (): void {
        Route::get('/inventario', [ProductoController::class, 'index'])->name('inventario.index');
        Route::post('/inventario', [ProductoController::class, 'store'])->name('inventario.store');

        Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
        Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
        Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
        Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');
        Route::post('/categorias/{categoria}/productos', [ProductoController::class, 'storeInCategory'])
            ->name('categorias.productos.store');
        Route::match(['put', 'post'], '/productos/{producto}', [ProductoController::class, 'update'])
            ->name('productos.update');
        Route::delete('/productos/{producto}', [ProductoController::class, 'destroy'])
            ->name('productos.destroy');
        Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
        Route::post('/proveedores', [ProveedorController::class, 'store'])->name('proveedores.store');
        Route::post('/proveedores/{proveedor}/reabastecer', [ProveedorController::class, 'reabastecer'])
            ->name('proveedores.reabastecer');
        Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])
            ->name('proveedores.update');
        Route::delete('/proveedores/{proveedor}', [ProveedorController::class, 'destroy'])
            ->name('proveedores.destroy');
    });

    Route::middleware('role:administrador,cajero')->group(function (): void {
        Route::get('/clientes', [ClienteController::class, 'index'])->name('clientes.index');
        Route::post('/pos/clientes', [ClienteController::class, 'storeFromPos'])->name('pos.clientes.store');
        Route::get('/pos', [VentaController::class, 'pos'])->name('pos.index');
        Route::post('/pos', [VentaController::class, 'store'])->name('pos.store');
        Route::post('/pos/cobrar', [VentaController::class, 'store'])->name('pos.cobrar');
        Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
        Route::get('/ventas/{venta}/factura', [VentaController::class, 'downloadInvoice'])
            ->name('ventas.factura');
        Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
        Route::get('/reportes/pdf', [ReporteController::class, 'exportPdf'])->name('reportes.pdf');
    });

    Route::middleware('role:administrador')->group(function (): void {
        Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
        Route::post('/usuarios', [UsuarioController::class, 'store'])->name('usuarios.store');
        Route::put('/usuarios/{user}/rol', [UsuarioController::class, 'updateRole'])->name('usuarios.role.update');
    });
});
