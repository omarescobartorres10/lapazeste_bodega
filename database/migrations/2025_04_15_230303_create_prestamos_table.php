<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();            
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas')->nullOnDelete();
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('departamento_id')->nullable()->constrained('departamentos');
            $table->integer('cantidad');
            $table->string('destino');
            $table->string('entregado_por')->nullable();
            $table->string('recibido_por');
            $table->string('observacion')->nullable();
            $table->string('estado')->default('prestado'); // prestado o devuelto
            $table->timestamp('fecha_devolucion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
