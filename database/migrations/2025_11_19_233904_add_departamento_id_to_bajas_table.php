<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bajas', function (Blueprint $table) {
            $table->unsignedBigInteger('departamento_id')->nullable()->after('categoria_id');

            $table->foreign('departamento_id')
                ->references('id')->on('departamentos')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('bajas', function (Blueprint $table) {
            $table->dropForeign(['departamento_id']);
            $table->dropColumn('departamento_id');
        });
    }

};
