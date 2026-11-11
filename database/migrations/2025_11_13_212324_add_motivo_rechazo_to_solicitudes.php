<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->text('motivo_rechazo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn('motivo_rechazo');
        });
    }

};
