<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePagosInformadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pagos_informados', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('factura_id')->unsigned();
            $table->integer('user_id')->unsigned();
            
            // Datos del pago informado
            $table->float('importe_informado', 8, 2);
            $table->date('fecha_pago_informado');
            $table->string('tipo_transferencia', 20)->comment('CBU, TRANSFERENCIA, DEPOSITO');
            
            // Datos bancarios
            $table->string('banco_origen', 100)->nullable();
            $table->string('numero_operacion', 50)->nullable();
            $table->string('cbu_origen', 50)->nullable();
            $table->string('titular_cuenta', 100)->nullable();
            
            // Estado de validación
            $table->enum('estado', ['pendiente', 'aprobado', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->integer('validado_por')->unsigned()->nullable();
            $table->timestamp('fecha_validacion')->nullable();
            
            // Archivo adjunto (comprobante)
            $table->string('comprobante_path')->nullable();
            
            $table->foreign('factura_id')->references('id')->on('facturas');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('validado_por')->references('id')->on('users');
            
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
        Schema::dropIfExists('pagos_informados');
    }
}