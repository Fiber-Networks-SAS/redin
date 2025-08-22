<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateServiciosUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios_usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('servicio_id')->unsigned();

            $table->string('contrato_nro');
            $table->dateTime('contrato_fecha');
            $table->dateTime('alta_servicio');
            $table->integer('mes_alta')->unsigned()->nullable();
            $table->float('abono_mensual', 8, 2);
            $table->float('abono_proporcional', 8, 2);
            $table->float('costo_instalacion_base', 8, 2);
            $table->float('costo_instalacion', 8, 2);
            $table->integer('plan_pago')->unsigned();
            $table->string('comentario')->nullable();
                        
            $table->char('status', 1)->default(1);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('servicio_id')->references('id')->on('servicios');

            $table->unique(array('user_id', 'servicio_id'));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicios_usuarios');
    }
}
