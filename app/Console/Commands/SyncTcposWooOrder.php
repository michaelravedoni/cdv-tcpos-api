<?php

namespace App\Console\Commands;

use App\Http\Controllers\Sync\OrderController;
use Illuminate\Console\Command;

class SyncTcposWooOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:tcpos_woo_order';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync orders between tcpos and woo';

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
    public function handle(): int
    {
        $this->line('Synchronisation lauched');
        $orderController = new OrderController;
        $orderController->sync();
        $this->info('Synchronisation done. There are queued jobs launched.');
    }
}
