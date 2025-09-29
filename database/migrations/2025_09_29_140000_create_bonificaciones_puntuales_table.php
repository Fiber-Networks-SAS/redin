<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonificacionesPuntualesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonificaciones_puntuales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('factura_id');
            $table->decimal('importe', 10, 2);
            $table->string('descripcion');
            $table->json('afip_response')->nullable();
            $table->unsignedInteger('nota_credito_id')->nullable();
            $table->timestamps();

            $table->foreign('factura_id')->references('id')->on('facturas');
            $table->foreign('nota_credito_id')->references('id')->on('notas_credito');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bonificaciones_puntuales');
    }
}