<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->char('tipo', 1)->comment('0:Internet, 1:Telefono, 2:TV');

            $table->float('abono_mensual', 8, 2);
            $table->float('abono_proporcional', 8, 2)->nullable();
            $table->float('costo_instalacion', 8, 2)->nullable();
            $table->string('detalle')->nullable();
            $table->char('status', 1)->default(1);

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
        Schema::dropIfExists('servicios');
    }
}
