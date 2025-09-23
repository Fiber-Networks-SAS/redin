<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImporteIvaToFacturasDetallesTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('facturas_detalle', function (Blueprint $table) {
            $table->decimal('importe_iva', 15, 2)->nullable();
        });
    }
}