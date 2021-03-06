<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Sync\SyncController;

class SyncTcposWoo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:tcpos_woo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync between tcpos and woo';

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
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Synchronisation lauched');
        $syncController = new SyncController;
        $syncController->all();
        $this->info('Synchronisation done. There are queued jobs launched.');
    }
}
