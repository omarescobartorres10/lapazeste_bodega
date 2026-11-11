<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('traslados', function (Blueprint $table) {
            $table->unsignedBigInteger('solicitud_id')->nullable()->after('codigo_operacion');

            $table->foreign('solicitud_id')
                  ->references('id')
                  ->on('solicitudes')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('traslados', function (Blueprint $table) {
            $table->dropForeign(['solicitud_id']);
            $table->dropColumn('solicitud_id');
        });
    }
};