<?php

use App\Jobs\SyncProductUpdate;
use Codexshaper\WooCommerce\Facades\Product;

it('updates a product in woocommerce', function () {
    $id = 10;
    $data = ['name' => 'New Product Name'];

    Product::shouldReceive('update')
        ->once()
        ->with($id, $data)
        ->andReturn((object) $data);

    (new SyncProductUpdate($id, $data))->handle();
});
