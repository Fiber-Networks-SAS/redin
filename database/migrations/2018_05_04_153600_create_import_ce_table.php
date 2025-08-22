<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateImportCeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('import_ce', function (Blueprint $table) {
            $table->increments('id');
            $table->string('filename');
            $table->string('codigo');
            $table->integer('cod_empresa');
            $table->integer('nro_sucursal')->unsigned();
            $table->integer('nro_factura')->unsigned();           
            $table->integer('cod_cliente');
            $table->integer('cod_cliente_old');
            $table->float('importe', 8, 2);
            $table->dateTime('fecha');
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
        Schema::dropIfExists('import_ce');
    }
}
