<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSaldosFavorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saldos_favor', function (Blueprint $table) {
            $table->increments('id');

            // Cliente que tiene el saldo a favor
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');

            // Factura original que fue anulada y estaba pagada
            $table->integer('factura_anulada_id')->unsigned();
            $table->foreign('factura_anulada_id')->references('id')->on('facturas');

            // Nota de crédito generada
            $table->integer('nota_credito_id')->unsigned()->nullable();
            $table->foreign('nota_credito_id')->references('id')->on('notas_credito');

            // Período original
            $table->string('periodo')->comment('Formato: MM/YYYY');

            // Importes
            $table->float('importe_pagado', 8, 2)->comment('Monto que el cliente había pagado');
            $table->float('importe_utilizado', 8, 2)->default(0)->comment('Monto ya utilizado del saldo');
            $table->float('importe_disponible', 8, 2)->comment('Saldo disponible = pagado - utilizado');

            // Estado
            $table->enum('estado', ['pendiente', 'utilizado', 'parcial'])->default('pendiente');
            
            // Factura nueva donde se aplicó el saldo (si ya se usó)
            $table->integer('factura_nueva_id')->unsigned()->nullable();
            $table->foreign('factura_nueva_id')->references('id')->on('facturas');

            // Motivo y observaciones
            $table->text('observaciones')->nullable();

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
        Schema::dropIfExists('saldos_favor');
    }
}
