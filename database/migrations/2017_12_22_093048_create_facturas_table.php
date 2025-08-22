<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacturasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facturas', function (Blueprint $table) {
            
            $table->increments('id');

            // basicos
            $table->integer('user_id')->unsigned();
            $table->integer('nro_cliente')->unsigned();
            $table->integer('talonario_id')->unsigned();
            $table->integer('nro_factura')->unsigned();
            $table->string('periodo')->comment('Formato: MM/YYYY');
            $table->dateTime('fecha_emision');
            
            // importes
            $table->float('importe_subtotal', 8, 2);
            $table->float('importe_bonificacion', 8, 2);
            $table->float('importe_total', 8, 2);
            $table->dateTime('primer_vto_fecha');
            $table->string('primer_vto_codigo');

            // interes
            // $table->float('primer_vto_tasa', 8, 2);
            // $table->float('primer_vto_importe', 8, 2);

            $table->dateTime('segundo_vto_fecha');
            $table->float('segundo_vto_tasa', 8, 2);
            $table->float('segundo_vto_importe', 8, 2);
            $table->string('segundo_vto_codigo');

            $table->dateTime('tercer_vto_fecha')->nullable();
            $table->float('tercer_vto_tasa', 8, 2)->nullable();
            $table->float('tercer_vto_importe', 8, 2)->nullable();
            $table->string('tercer_vto_codigo')->nullable();


            // pago
            $table->float('importe_pago', 8, 2)->nullable();
            $table->dateTime('fecha_pago')->nullable();
            $table->char('forma_pago', 1)->nullable()->comment('1:efectivo, 2:pagomiscuentas, 3:cobroexpress, 4:CC, 5:deposito');
            $table->string('lote')->nullable()->comment('Nombre del archivo importado de pagomiscuentas o cobroexpress');
            
            // fecha de la notificacion por mail
            $table->string('mail_to')->nullable();
            $table->dateTime('mail_date')->nullable();


            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('talonario_id')->references('id')->on('talonarios');
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
        Schema::dropIfExists('facturas');
    }
}
