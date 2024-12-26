<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('minQuantity')->nullable();
            $table->string('maxQuantity')->nullable();
            $table->string('category')->nullable();
            $table->string('weight')->nullable();
            $table->float('vatInPercent')->nullable();
            $table->string('hash')->nullable();
            $table->string('sync_action')->nullable();

            $table->string('description')->nullable();
            $table->integer('isAddition')->nullable();
            $table->integer('articleOrder')->nullable();
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
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
