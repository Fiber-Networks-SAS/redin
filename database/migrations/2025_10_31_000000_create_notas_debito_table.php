<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotasDebitoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notas_debito', function (Blueprint $table) {
            $table->increments('id');

            // Relación con factura original
            $table->integer('factura_id')->unsigned();
            $table->foreign('factura_id')->references('id')->on('facturas');

            // Datos de la nota de débito
            $table->integer('talonario_id')->unsigned();
            $table->foreign('talonario_id')->references('id')->on('talonarios');
            $table->integer('nro_nota_debito')->unsigned();

            // Importes
            $table->float('importe_ampliacion', 8, 2);
            $table->float('importe_iva', 8, 2)->nullable();
            $table->float('importe_total', 8, 2);

            // Datos AFIP
            $table->string('cae')->nullable();
            $table->dateTime('cae_vto')->nullable();
            $table->dateTime('fecha_emision');

            // Motivo de la ampliación
            $table->text('motivo')->nullable();

            // Campos adicionales
            $table->integer('nro_cliente')->nullable();
            $table->string('periodo')->nullable();

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
        Schema::dropIfExists('notas_debito');
    }
}

