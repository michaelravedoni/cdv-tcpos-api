<?php

use App\Models\Product;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Attribute;
use Codexshaper\WooCommerce\Facades\Term;
use Codexshaper\WooCommerce\Facades\Customer;
use Illuminate\Support\Facades\Queue;

test('le point d\'entree sync all renvoie une reponse JSON correcte', function () {
    $this->withoutExceptionHandling();
    Queue::fake();
    
    Attribute::shouldReceive('all')->andReturn(collect());
    Term::shouldReceive('all')->andReturn(collect());
    Customer::shouldReceive('all')->andReturn(collect());

    $response = $this->getJson('/api/sync/all');

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'Sync launched. Wait and see /jobs']);
});
