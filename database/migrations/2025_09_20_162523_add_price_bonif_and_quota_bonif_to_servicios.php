<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceBonifAndQuotaBonifToServicios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bonificaciones_servicios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('service_id');
            $table->decimal('porcentaje_bonificacion', 5, 2)->comment('Porcentaje de bonificación (0-100)');
            $table->integer('periodos_bonificacion')->comment('Número de períodos que durará la bonificación');
            $table->date('fecha_inicio')->comment('Fecha de inicio de la bonificación');
            $table->boolean('activo')->default(true);
            $table->text('descripcion')->nullable();
            $table->foreign('service_id')->references('id')->on('servicios');
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
        Schema::dropIfExists('bonificaciones_servicios');
    }
}
