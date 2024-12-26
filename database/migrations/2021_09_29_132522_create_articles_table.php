<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->json('priceLevelCodes')->nullable();
            //$table->float('vatInPercent')->nullable();
            $table->string('hash')->nullable();
            $table->string('sync_action')->nullable();

            $table->string('description')->nullable();
            $table->string('printoutNotes')->nullable();
            $table->string('notes1')->nullable();
            $table->string('notes2')->nullable();
            $table->string('notes3')->nullable();
            $table->integer('groupACode')->nullable();
            $table->integer('groupBCode')->nullable();
            $table->integer('groupCCode')->nullable();
            $table->integer('groupDCode')->nullable();
            $table->integer('groupECode')->nullable();
            $table->integer('groupFCode')->nullable();

            $table->integer('_tcposId')->nullable();
            $table->string('_tcposCode')->nullable();
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
        Schema::dropIfExists('articles');
    }
};
