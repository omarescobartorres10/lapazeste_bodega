<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('entradas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->foreignId('categoria_id')->constrained('categorias');
            $table->foreignId('producto_id')->constrained('productos');
            $table->foreignId('bodega_id')->constrained('bodegas');
            $table->integer('cantidad');
            $table->string('recibido_por')->nullable();
            $table->string('entregado_por')->nullable();
            $table->string('observacion')->nullable();
            $table->boolean('estado')->default(1);

            $table->string('numero_factura')->nullable();
            $table->string('numero_orden_compra')->nullable();
            $table->string('factura_pdf')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entradas');
    }
};
