<?php

use App\Models\Product;

test('api retourne les statistiques des produits', function () {
    Product::factory()->count(5)->create(['category' => 'wine']);
    Product::factory()->count(2)->create(['category' => 'beer']);

    $response = $this->getJson('/api/info');

    $response->assertStatus(200)
        ->assertJsonFragment(['products_count' => 7])
        ->assertJsonFragment(['count_wine' => 5]);
});
