<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('reabastecimientos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('proveedor_id')->constrained('proveedors')->cascadeOnDelete();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('cantidad', 10, 3);
            $table->decimal('costo_unitario', 12, 2)->nullable();
            $table->decimal('costo_total', 12, 2)->nullable();
            $table->string('codigo_lote', 50)->nullable()->index();
            $table->dateTime('fecha_recepcion')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reabastecimientos');
    }
};
