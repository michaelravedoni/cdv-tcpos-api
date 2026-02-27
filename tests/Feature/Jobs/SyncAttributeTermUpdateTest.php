<?php

use App\Jobs\SyncAttributeTermUpdate;
use Codexshaper\WooCommerce\Facades\Term;

it('updates a term in woocommerce', function () {
    $id = 5;
    $data = ['name' => 'Red'];
    $attributeId = 2; // cellar attribute id

    config(['cdv.wc_attribute_ids.cellar' => $attributeId]);

    Term::shouldReceive('update')
        ->once()
        ->with($attributeId, $id, $data)
        ->andReturn((object) $data);

    (new SyncAttributeTermUpdate($id, $data))->handle();
});
