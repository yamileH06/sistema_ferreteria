<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table): void {
            $table->string('codigo_venta')->nullable()->unique();
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->string('moneda', 3)->default('COP');
            $table->dateTime('fecha_venta')->nullable();
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->decimal('stock', 10, 3)->default(0)->change();
        });

        Schema::table('detalle_ventas', function (Blueprint $table): void {
            $table->decimal('cantidad', 10, 3)->change();
        });

        DB::table('ventas')
            ->select(['id', 'total', 'created_at'])
            ->orderBy('id')
            ->chunkById(500, function ($sales): void {
                $saleIds = $sales->pluck('id');
                $subtotals = DB::table('detalle_ventas')
                    ->whereIn('venta_id', $saleIds)
                    ->select('venta_id')
                    ->selectRaw('SUM(subtotal) as subtotal')
                    ->groupBy('venta_id')
                    ->pluck('subtotal', 'venta_id');

                foreach ($sales as $sale) {
                    DB::table('ventas')
                        ->where('id', $sale->id)
                        ->update([
                            'codigo_venta' => 'VTA-'.str_pad((string) $sale->id, 8, '0', STR_PAD_LEFT),
                            'subtotal' => $subtotals->get($sale->id, $sale->total),
                            'fecha_venta' => $sale->created_at ?? now(),
                        ]);
                }
            });

        Schema::table('ventas', function (Blueprint $table): void {
            $table->string('codigo_venta')->nullable(false)->change();
            $table->decimal('subtotal', 12, 2)->nullable(false)->change();
            $table->dateTime('fecha_venta')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasFractionalStock = DB::table('productos')
            ->select('stock')
            ->cursor()
            ->contains(fn (object $product): bool => (float) $product->stock !== floor((float) $product->stock));
        $hasFractionalQuantity = DB::table('detalle_ventas')
            ->select('cantidad')
            ->cursor()
            ->contains(fn (object $detail): bool => (float) $detail->cantidad !== floor((float) $detail->cantidad));

        if ($hasFractionalStock || $hasFractionalQuantity) {
            throw new RuntimeException(
                'No se puede revertir esta migración mientras existan cantidades fraccionarias; convertirlas a enteros perdería datos.',
            );
        }

        Schema::table('detalle_ventas', function (Blueprint $table): void {
            $table->integer('cantidad')->change();
        });

        Schema::table('productos', function (Blueprint $table): void {
            $table->integer('stock')->default(0)->change();
        });

        Schema::table('ventas', function (Blueprint $table): void {
            $table->dropUnique(['codigo_venta']);
            $table->dropColumn(['codigo_venta', 'subtotal', 'moneda', 'fecha_venta']);
        });
    }
};
