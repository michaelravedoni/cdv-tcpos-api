<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Codexshaper\WooCommerce\Facades\Customer;

class SyncCustomerUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $wooCustomer;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($wooCustomer)
    {
        $this->wooCustomer = $wooCustomer;
        $this->is = $wooCustomer->id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $customer_controller = new \App\Http\Controllers\Api\V1\CustomerController;
        $tcposCustomerFund = $customer_controller->getCustomerFunds(data_get($this->wooCustomer, 'card_number'));
        $tcposCustomer = $customer_controller->getCustomer(data_get($this->wooCustomer, 'card_number'));
        $tcposCustomerAccountType = data_get($tcposCustomer->original, 'accountType');
        $data = [
            'account_funds' => $tcposCustomerFund,
            'meta_data' => [
                config('cdv.wc_meta_customer_account_type') => $tcposCustomerAccountType,
            ],
        ];
        
        // https://codexshaper.github.io/docs/laravel-woocommerce/#update-customer
        //Customer::update($this->id, $data);
    }
}
