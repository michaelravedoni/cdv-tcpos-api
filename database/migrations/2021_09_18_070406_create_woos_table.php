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
     */
    public function down(): void
    {
        Schema::dropIfExists('woos');
    }
};
