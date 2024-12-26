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
            report('Queue jobs table should be emptied by now but it is not! Please check your queue worker.');

            Mail::raw('Hello, queue jobs problem in '.config('app.name').' application. Queue jobs table should be emptied by now but it is not! Please check the queue worker manually. URL : '.config('app.url'), function ($message) {
                $message->to('michael@ravedoni.com')->subject(config('app.name').' - Queue problem');
            });

            $this->warn('Queue jobs table should be emptied by now but it is not! Please check your queue worker.');

            return self::SUCCESS;
        }

        $this->info('Queue jobs are looking good.');
    }
}
