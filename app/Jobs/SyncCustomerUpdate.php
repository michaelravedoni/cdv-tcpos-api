<?php

namespace App\Jobs;

use Codexshaper\WooCommerce\Facades\Customer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class SyncCustomerUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $id;

    public $data;

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
    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // https://codexshaper.github.io/docs/laravel-woocommerce/#update-customer
        Customer::update($this->id, $this->data);

        activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'customers'])->log('Customer updated in Woocommerce | WooId:'.$this->id);
    }
}
