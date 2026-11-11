<?php

// database/migrations/2025_01_01_000001_create_solicitud_detalles_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('solicitud_detalles', function (Blueprint $table) {
            $table->id();

            $table->foreignId('solicitud_id')
                  ->constrained('solicitudes')
                  ->onDelete('cascade');

            $table->foreignId('producto_id')
                  ->constrained('productos')
                  ->cascadeOnDelete();

            $table->integer('cantidad');
            $table->string('observacion')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_detalles');
    }
};
