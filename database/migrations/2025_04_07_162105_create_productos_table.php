<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crea la tabla productos (con campo departamento_id incluido)
     */
    public function up(): void
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            // 🔹 Código (ya no es globalmente único)
            $table->string('codigo');
            $table->string('nombre');
            $table->text('descripcion')->nullable();
            $table->integer('stock')->default(0);

            // 🔹 Relación con departamentos
            $table->foreignId('departamento_id')
                  ->nullable()
                  ->constrained('departamentos')
                  ->onDelete('set null');

            // 🔹 Relación con bodegas
            $table->foreignId('bodega_id')
                  ->nullable()
                  ->constrained('bodegas')
                  ->onDelete('set null');

            // 🔹 Relación con categorías
            $table->foreignId('categoria_id')
                  ->constrained('categorias')
                  ->onDelete('cascade');

            // 🔹 Relación con tipo de producto
            $table->foreignId('tipo_producto_id')
                  ->constrained('tipos_productos')
                  ->onDelete('cascade');

            // 🔹 Relación con unidades
            $table->foreignId('unidades_id')
                  ->constrained('unidades')
                  ->onDelete('cascade');

            // 🔹 Estado y timestamps
            $table->boolean('estado')->default(1);
            $table->timestamps();

            // 🔹 Código único por bodega
            $table->unique(['codigo', 'bodega_id']);
        });
    }

    /**
     * Elimina la tabla productos
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
