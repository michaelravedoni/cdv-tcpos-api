<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWoosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('woos', function (Blueprint $table) {
            $table->id();
            $table->integer('_wooId')->nullable();
            $table->string('resource')->nullable();
            $table->integer('_tcposId')->nullable();
            $table->string('_tcposCode')->nullable();
            $table->json('data')->nullable();
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
        Schema::dropIfExists('woos');
    }
}
