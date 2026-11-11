<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('salidas', function (Blueprint $table) {
            $table->unsignedBigInteger('departamento_id')->nullable()->after('bodega_id');

            $table->foreign('departamento_id')
                ->references('id')
                ->on('departamentos');
        });
    }

    public function down()
    {
        Schema::table('salidas', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }

};
