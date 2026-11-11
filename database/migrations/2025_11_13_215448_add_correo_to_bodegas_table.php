<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bodegas', function (Blueprint $table) {
            $table->string('correo')->nullable()->after('responsable');
        });
    }

    public function down()
    {
        Schema::table('bodegas', function (Blueprint $table) {
            $table->dropColumn('correo');
        });
    }

};
