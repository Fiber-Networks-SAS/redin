<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReclamosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reclamos', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_from')->unsigned();
            $table->integer('user_to')->default(0)->unsigned();

            $table->integer('servicio_id')->default(0)->unsigned();
            
            $table->string('titulo');
            $table->longText('mensaje');

            $table->char('leido_client', 1)->default(0)->comment('0:no leido, 1:leido');
            $table->char('leido_admin', 1)->default(0)->comment('0:no leido, 1:leido');
            $table->char('status', 1)->default(0)->comment('0:abierto, 1:cerrado');

            $table->integer('parent_id')->default(0)->unsigned();
            $table->string('replys')->nullable();

            // $table->foreign('user_id')->references('id')->on('users');
            // $table->foreign('user_to')->references('id')->on('users');

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
        Schema::dropIfExists('reclamos');
    }
}
