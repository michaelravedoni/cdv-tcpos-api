<?php

use App\Jobs\SyncCustomerUpdate;
use Codexshaper\WooCommerce\Facades\Customer;

it('updates a customer in woocommerce', function () {
    $id = 50;
    $data = ['first_name' => 'John'];

    Customer::shouldReceive('update')
        ->once()
        ->with($id, $data)
        ->andReturn((object) $data);

    (new SyncCustomerUpdate($id, $data))->handle();
});
