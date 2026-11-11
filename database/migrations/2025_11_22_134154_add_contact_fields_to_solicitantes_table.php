<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('solicitantes', function (Blueprint $table) {
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
        });
    }

    public function down()
    {
        Schema::table('solicitantes', function (Blueprint $table) {
            $table->dropColumn(['telefono', 'correo']);
        });
    }

};
