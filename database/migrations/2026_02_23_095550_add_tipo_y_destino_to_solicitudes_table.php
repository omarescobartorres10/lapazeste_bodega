<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('tipo_movimiento', 20)->nullable()->after('bodega_id');
            $table->unsignedBigInteger('bodega_destino_id')->nullable()->after('tipo_movimiento');

            // Opcional: FK (recomendado)
            $table->foreign('bodega_destino_id')->references('id')->on('bodegas');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropForeign(['bodega_destino_id']);
            $table->dropColumn(['tipo_movimiento', 'bodega_destino_id']);
        });
    }
};