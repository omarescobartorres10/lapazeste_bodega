<?php

// database/migrations/2025_01_01_000000_create_solicitudes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();

            // Código único tipo SOL-00001
            $table->string('codigo')->unique();

            $table->string('solicitante');
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->string('departamento_solicitante')->nullable();
            $table->text('comentario')->nullable();

            // pendiente / aprobado / rechazado
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])
                  ->default('pendiente');

            // bodega desde donde se surtirá
            $table->foreignId('bodega_id')
                  ->nullable()
                  ->constrained('bodegas')
                  ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
