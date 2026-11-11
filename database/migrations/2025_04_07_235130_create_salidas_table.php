<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalidasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('salidas', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('categoria_id')->constrained('categorias')->onDelete('cascade');
            $table->foreignId('producto_id')->constrained('productos')->onDelete('cascade');
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas')->nullOnDelete();

            $table->integer('cantidad');
            $table->string('destino');
            $table->string('recibido_por');
            $table->string('entregado_por')->nullable();
            $table->string('observacion')->nullable();

            $table->boolean('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('salidas');
    }
}
