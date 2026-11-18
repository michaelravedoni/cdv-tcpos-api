<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class CheckQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:db-monitor';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if our database queue is still running';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /**
         * Because we use a database queue, we check if the jobs table still contains any
         * old records. This means that the queue has been stalled.
         */
        $records = DB::table('jobs')->where('created_at', '<', Carbon::now()->subMinutes(20)->getTimestamp())->get();

        if (! $records->isEmpty()) {
            $totalPending = DB::table('jobs')->count();
            $totalFailed = DB::table('failed_jobs')->count();
            $oldestCreatedAt = $records->min('created_at');
            $oldestMinutes = $oldestCreatedAt ? Carbon::createFromTimestamp($oldestCreatedAt)->diffInMinutes(now()) : 0;
            $appUrl = rtrim((string) config('app.url'), '/');

            report('Queue jobs table should be emptied by now but it is not! Please check your queue worker.');

            $body = implode("\n", [
                'Bonjour,',
                '',
                'La file de jobs de ' . config('app.name') . ' semble bloquée ou saturée :',
                "- Jobs en attente : {$totalPending} (dont {$records->count()} de plus de 20 minutes)",
                "- Plus vieux job en attente depuis : {$oldestMinutes} minutes",
                "- Jobs échoués (failed_jobs) : {$totalFailed}",
                '',
                "Vérifier la file : {$appUrl}/jobs",
                "Vérifier les logs : {$appUrl}/logs",
            ]);

            Mail::raw($body, function ($message) {
                $message->to('michael@ravedoni.com')->subject(config('app.name').' - Queue problem');
            });

            $this->warn('Queue jobs table should be emptied by now but it is not! Please check your queue worker.');
        }

        $this->info('Queue jobs are looking good.');
        
        return self::SUCCESS;
    }
}
