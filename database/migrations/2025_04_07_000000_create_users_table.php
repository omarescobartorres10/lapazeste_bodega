<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('usuario')->unique();
            $table->string('password');
            
            // 🔹 Si es null => usuario administrador (acceso total)
            // Si tiene valor => acceso solo a su bodega asignada
            $table->foreignId('bodega_id')->nullable()->constrained('bodegas')->nullOnDelete();

            $table->boolean('estado')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
