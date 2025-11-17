<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHomeSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('home_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->nullable(); // texto, imagen, etc.
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_settings');
    }
}
