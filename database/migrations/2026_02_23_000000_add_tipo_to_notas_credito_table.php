<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTipoToNotasCreditoTable extends Migration
{
    public function up()
    {
        Schema::table('notas_credito', function (Blueprint $table) {
            // 'bonificacion' = NC que reduce monto de factura (flujo existente)
            // 'correccion'   = NC solo en AFIP para corregir error de facturación (no modifica factura)
            $table->string('tipo', 20)->default('bonificacion')->after('periodo');
        });
    }

    public function down()
    {
        Schema::table('notas_credito', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });
    }
}
