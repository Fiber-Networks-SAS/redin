<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTalonariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('talonarios', function (Blueprint $table) {
            $table->increments('id');

            $table->char('letra', 1);
            $table->string('nombre');

            $table->integer('nro_punto_vta');
            $table->integer('nro_inicial');
            $table->bigInteger('nro_cai');
            $table->date('nro_cai_fecha_vto');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('talonarios');
    }
}
