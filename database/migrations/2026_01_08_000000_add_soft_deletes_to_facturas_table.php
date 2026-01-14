<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletesToFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->softDeletes();
            $table->string('motivo_anulacion')->nullable()->comment('Motivo de anulación del período');
            $table->integer('anulado_por')->unsigned()->nullable()->comment('ID del usuario que anuló');
            $table->dateTime('fecha_anulacion')->nullable()->comment('Fecha de anulación');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['motivo_anulacion', 'anulado_por', 'fecha_anulacion']);
        });
    }
}
