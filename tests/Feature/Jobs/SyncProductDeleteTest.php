<?php

use App\Jobs\SyncProductDelete;
use Codexshaper\WooCommerce\Facades\Product;

it('deletes a product from woocommerce', function () {
    $id = 10;

    Product::shouldReceive('delete')
        ->once()
        ->with($id, ['force' => false])
        ->andReturn((object) ['id' => $id]);

    (new SyncProductDelete($id))->handle();
});
