<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->json('pictures')->nullable();
            $table->integer('priceTakeAway')->nullable();
            $table->json('attributes')->nullable();
            $table->string('minQuantity')->nullable();
            $table->string('maxQuantity')->nullable();
            $table->string('stockQty')->nullable();
            $table->string('category')->nullable();
            $table->string('weight')->nullable();

            $table->integer('vatInPercent')->nullable();
            $table->string('measureUnitId')->nullable();
            $table->string('printoutNotes')->nullable();
            $table->string('notes1')->nullable();
            $table->string('notes2')->nullable();
            $table->string('notes3')->nullable();
            $table->integer('groupAId')->nullable();
            $table->integer('groupBId')->nullable();
            $table->integer('groupCId')->nullable();
            $table->integer('groupDId')->nullable();
            $table->integer('groupEId')->nullable();
            $table->integer('groupFId')->nullable();

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
        Schema::dropIfExists('products');
    }
}
