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
        Schema::table('queue_monitor', function (Blueprint $table) {
            if (!Schema::hasColumn('queue_monitor', 'queued_at')) {
                $table->dateTime('queued_at')->nullable()->after('status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('queue_monitor', function (Blueprint $table) {
            if (Schema::hasColumn('queue_monitor', 'queued_at')) {
                $table->dropColumn('queued_at');
            }
        });
    }
};
