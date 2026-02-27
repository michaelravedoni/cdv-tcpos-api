<?php

use App\Jobs\SyncOrderUpdate;
use Codexshaper\WooCommerce\Facades\Order;

it('updates an order in woocommerce', function () {
    $id = 100;
    $data = ['status' => 'completed'];

    Order::shouldReceive('update')
        ->once()
        ->with($id, $data)
        ->andReturn((object) $data);

    (new SyncOrderUpdate($id, $data))->handle();
});
