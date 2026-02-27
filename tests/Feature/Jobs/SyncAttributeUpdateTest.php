<?php

use App\Jobs\SyncAttributeUpdate;
use Codexshaper\WooCommerce\Facades\Attribute;

it('updates an attribute in woocommerce', function () {
    $id = 1;
    $data = ['name' => 'Color'];

    Attribute::shouldReceive('update')
        ->once()
        ->with($id, $data)
        ->andReturn((object) $data);

    (new SyncAttributeUpdate($id, $data))->handle();
});
