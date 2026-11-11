<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->enum('resolucion', ['A_LA_ESPERA', 'ENTREGADO'])
                ->default('A_LA_ESPERA')
                ->after('estado');
        });
    }

    public function down()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn('resolucion');
        });
    }

};
