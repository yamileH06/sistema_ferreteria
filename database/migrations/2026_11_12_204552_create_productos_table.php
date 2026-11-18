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
       
Schema::create('productos', function (Blueprint $table) {
    $table->id();
    $table->string('nombre');
    $table->string('codigo_sku')->unique();
    $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
    $table->foreignId('proveedor_id')->nullable()->constrained('proveedors')->nullOnDelete();
    $table->integer('stock')->default(0);
    $table->decimal('precio_compra', 12, 2);
    $table->decimal('precio_venta', 12, 2); // Precio en COP
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
