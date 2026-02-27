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
        Schema::table('articles', function (Blueprint $table) {
            $table->text('name')->nullable()->change();
            $table->text('description')->nullable()->change();
            $table->text('printoutNotes')->nullable()->change();
            $table->text('notes1')->nullable()->change();
            $table->text('notes2')->nullable()->change();
            $table->text('notes3')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('description')->nullable()->change();
            $table->string('printoutNotes')->nullable()->change();
            $table->string('notes1')->nullable()->change();
            $table->string('notes2')->nullable()->change();
            $table->string('notes3')->nullable()->change();
        });
    }
};
