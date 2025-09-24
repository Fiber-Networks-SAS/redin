<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIvaFieldsToFacturas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('facturas', function (Blueprint $table) {
            $table->decimal('importe_subtotal_iva', 10, 2)->after('importe_subtotal');
            $table->decimal('importe_bonificacion_iva', 10, 2)->after('importe_bonificacion');
        });
    }

}
