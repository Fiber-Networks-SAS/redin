<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_preferences', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('factura_id');
            $table->enum('vencimiento_tipo', ['primer', 'segundo', 'tercer']);
            $table->string('preference_id')->nullable();
            $table->text('init_point')->nullable();
            $table->longText('qr_code_base64')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('external_reference')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->string('payment_id')->nullable();
            $table->string('payment_status')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            // Índices
            $table->foreign('factura_id')->references('id')->on('facturas')->onDelete('cascade');
            $table->index(['factura_id', 'vencimiento_tipo']);
            $table->index('status');
            $table->index('external_reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_preferences');
    }
}