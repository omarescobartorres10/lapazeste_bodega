<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTiposProductosTable extends Migration
{
    public function up()
    {
        Schema::create('tipos_productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');  // Nombre del tipo de producto (Ej. Descartable, Préstamo/Devolución)
            $table->boolean('estado')->default(1); // 1 = Activo, 0 = Inactivo
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tipos_productos');
    }
}

