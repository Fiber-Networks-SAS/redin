<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasDetalleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas_detalle', function (Blueprint $table) {
            $table->increments('id');

            // referencias
            $table->integer('factura_id')->unsigned();
            $table->integer('servicio_id')->unsigned();

            // importes
            $table->float('abono_mensual', 8, 2)->nullable();
            $table->float('abono_proporcional', 8, 2)->nullable();
            $table->integer('dias_proporcional')->nullable()->unsigned();
            $table->float('costo_instalacion', 8, 2)->nullable();
            $table->integer('instalacion_cuota')->nullable()->unsigned();
            $table->integer('instalacion_plan_pago')->nullable()->unsigned();

            $table->foreign('factura_id')->references('id')->on('facturas');
            $table->foreign('servicio_id')->references('id')->on('servicios');
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
        Schema::dropIfExists('facturas_detalle');
    }
}
