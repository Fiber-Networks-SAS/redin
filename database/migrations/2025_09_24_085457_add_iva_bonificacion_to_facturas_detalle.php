<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIvaBonificacionToFacturasDetalle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas_detalle', function (Blueprint $table) {
            $table->decimal('iva_bonificacion', 10, 2)->nullable();
        });
    }

}
