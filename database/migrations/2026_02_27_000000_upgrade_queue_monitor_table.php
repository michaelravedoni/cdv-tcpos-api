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
        Schema::table(config('queue-monitor.table'), function (Blueprint $table) {
            if (!Schema::hasColumn(config('queue-monitor.table'), 'status')) {
                $table->integer('status')->default(0)->index()->after('time_elapsed');
            }
            if (!Schema::hasColumn(config('queue-monitor.table'), 'retried')) {
                $table->boolean('retried')->default(false)->after('status');
            }
            if (!Schema::hasColumn(config('queue-monitor.table'), 'job_uuid')) {
                $table->string('job_uuid')->nullable()->after('job_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(config('queue-monitor.table'), function (Blueprint $table) {
            $table->dropColumn(['status', 'retried', 'job_uuid']);
        });
    }
};
