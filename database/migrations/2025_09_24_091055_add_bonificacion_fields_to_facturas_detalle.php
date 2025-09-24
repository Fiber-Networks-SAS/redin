<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonificacionFieldsToFacturasDetalle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas_detalle', function (Blueprint $table) {
            $table->decimal('importe_bonificacion', 10, 2)->nullable();
            $table->text('bonificacion_detalle')->nullable();
        });
    }

}
