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
       Schema::create('ventas', function (Blueprint $table) {
    $table->id();
    $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->decimal('total', 12, 2); // Total en COP
    $table->string('metodo_pago'); // Efectivo, Nequi, Bancolombia, Tarjeta, Crédito
    $table->string('estado')->default('completada'); // completada, pendiente (para fiados)
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
