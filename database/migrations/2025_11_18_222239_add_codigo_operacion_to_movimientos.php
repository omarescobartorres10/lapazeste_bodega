<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->string('codigo_operacion')->nullable()->after('id');
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->string('codigo_operacion')->nullable()->after('id');
        });

        Schema::table('prestamos', function (Blueprint $table) {
            $table->string('codigo_operacion')->nullable()->after('id');
        });

        Schema::table('bajas', function (Blueprint $table) {
            $table->string('codigo_operacion')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('entradas', function (Blueprint $table) {
            $table->dropColumn('codigo_operacion');
        });

        Schema::table('salidas', function (Blueprint $table) {
            $table->dropColumn('codigo_operacion');
        });

        Schema::table('prestamos', function (Blueprint $table) {
            $table->dropColumn('codigo_operacion');
        });

        Schema::table('bajas', function (Blueprint $table) {
            $table->dropColumn('codigo_operacion');
        });
    }

};
