<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('solicitantes', function (Blueprint $table) {
            $table->id();
            $table->string('dui', 10)->unique();
            $table->string('codigo_marcador', 20);
            $table->string('nombre', 255);
            $table->string('cargo', 255)->nullable();
            $table->foreignId('departamento_id')->constrained('departamentos');
            $table->enum('estado', ['activo','inactivo'])->default('activo');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('solicitantes');
    }

};
