<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('traslados', function (Blueprint $table) {
            $table->id();

            $table->string('codigo_operacion')->index();

            $table->unsignedBigInteger('bodega_origen_id');
            $table->unsignedBigInteger('bodega_destino_id');

            $table->unsignedBigInteger('producto_id_origen');
            $table->unsignedBigInteger('producto_id_destino');

            $table->unsignedBigInteger('categoria_id')->nullable();
            $table->unsignedBigInteger('departamento_id')->nullable();

            $table->integer('cantidad');
            $table->string('entregado_por')->nullable();
            $table->string('recibido_por')->nullable();
            $table->string('observacion')->nullable();

            // 1 = activo, 0 = cancelado (igual que salidas)
            $table->tinyInteger('estado')->default(1);

            $table->timestamps();

            $table->foreign('bodega_origen_id')->references('id')->on('bodegas');
            $table->foreign('bodega_destino_id')->references('id')->on('bodegas');

            $table->foreign('producto_id_origen')->references('id')->on('productos');
            $table->foreign('producto_id_destino')->references('id')->on('productos');

            $table->foreign('categoria_id')->references('id')->on('categorias');
            $table->foreign('departamento_id')->references('id')->on('departamentos');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('traslados');
    }
};