<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            // vacío, lo hacemos fuera del closure
        });

        if (!Schema::hasColumn('solicitudes', 'fecha_devolucion')) {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->date('fecha_devolucion')->nullable()->after('fecha_requerida');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('solicitudes', 'fecha_devolucion')) {
            Schema::table('solicitudes', function (Blueprint $table) {
                $table->dropColumn('fecha_devolucion');
            });
        }
    }
};