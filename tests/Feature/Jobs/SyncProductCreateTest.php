<?php

use App\Jobs\SyncProductCreate;
use Codexshaper\WooCommerce\Facades\Product;

it('creates a product in woocommerce', function () {
    $data = ['name' => 'New Product'];

    Product::shouldReceive('create')
        ->once()
        ->with($data)
        ->andReturn((object) $data);

    (new SyncProductCreate($data))->handle();
});
