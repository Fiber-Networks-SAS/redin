<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {

            $table->increments('id');
            
            // datos personales
            $table->integer('nro_cliente')->nullable();
            $table->bigInteger('dni')->nullable();
            $table->string('email')->nullable();
            $table->string('password');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            
            $table->string('barrio')->nullable();
            $table->string('calle')->nullable();
            $table->string('altura')->nullable();
            $table->string('manzana')->nullable();

            $table->integer('provincia')->nullable();
            $table->integer('localidad')->nullable();
            $table->integer('cp')->nullable();

            $table->string('tel1')->nullable();
            $table->string('tel2')->nullable();

            $table->string('picture')->nullable();
            $table->string('autorizado_nombre')->nullable();
            $table->string('autorizado_tel')->nullable();
            $table->dateTime('fecha_registro')->nullable()->comment('Fecha de registro en sistema de Autogestión');
            
            // datos tecnicos
            $table->char('drop', 1)->nullable()->comment('0:En Pilar, 1:En Domicilio');
            $table->dateTime('firma_contrato')->nullable()->comment('Contrato Firmado');
            
            $table->dateTime('ont_instalado')->nullable()->comment('Fecha de Instalacion');
            $table->dateTime('ont_funcionando')->nullable()->comment('Fecha Funcional');
            $table->string('ont_serie1')->nullable();
            $table->string('ont_serie2')->nullable();
            $table->integer('instalador_id')->nullable();
            $table->string('spliter_serie')->nullable();

            $table->integer('talonario_id')->nullable();
            $table->string('comentario')->nullable();
          


            $table->char('status', 1)->default(0);
            $table->rememberToken();
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
        Schema::drop('users');
    }
}
