<?php

namespace App\Jobs;

use Codexshaper\WooCommerce\Facades\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class SyncProductDelete implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $id;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // https://codexshaper.github.io/docs/laravel-woocommerce/#delete-product
        Product::delete($this->id, ['force' => false]);

        activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'products'])->log('Product deleted from Woocommerce | WooId:'.$this->id);
    }
}
