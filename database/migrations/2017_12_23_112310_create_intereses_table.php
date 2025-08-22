<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInteresesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intereses', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('primer_vto_dia')->unsigned();
            // $table->float('primer_vto_tasa', 8, 2);
            $table->integer('segundo_vto_dia')->unsigned()->nullable();
            $table->float('segundo_vto_tasa', 8, 2)->nullable();
            $table->float('tercer_vto_tasa', 8, 2)->nullable();

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
        Schema::dropIfExists('intereses');
    }
}
