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
        Schema::create('bajas', function (Blueprint $table) {
            $table->id();

            // 🔹 Relación con la bodega donde ocurre la baja
            $table->foreignId('bodega_id')
                  ->constrained('bodegas')
                  ->onDelete('cascade');

            // 🔹 Relación con categoría
            $table->foreignId('categoria_id')
                  ->constrained('categorias')
                  ->onDelete('cascade');

            // 🔹 Relación con producto
            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->onDelete('cascade');

            // 🔹 Campos propios de la baja
            $table->integer('cantidad'); // cantidad retirada del inventario
            $table->text('motivo');      // motivo de la baja (dañado, vencido, etc.)
            $table->string('responsable')->nullable(); // persona responsable

            // 🔹 Estado: 1 = procesado | 0 = cancelado
            $table->boolean('estado')->default(1);

            $table->timestamps(); // created_at / updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bajas');
    }
};
