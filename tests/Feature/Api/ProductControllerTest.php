<?php

use App\Models\Product;
use Illuminate\Support\Facades\Http;

test('index retourne les produits filtrés', function () {
    Product::factory()->create(['category' => 'wine']);
    Product::factory()->create(['category' => 'none']);

    $response = $this->getJson('/api/products');

    $response->assertStatus(200)
        ->assertJsonCount(1); // 'none' est exclu dans le contrôleur
});

test('indexByCategory filtre correctement', function () {
    Product::factory()->create(['category' => 'wine']);
    Product::factory()->create(['category' => 'beer']);

    $response = $this->getJson('/api/products/wine');

    $response->assertStatus(200)
        ->assertJsonCount(1)
        ->assertJsonFragment(['category' => 'wine']);
});

test('getProducts récupère les données brutes de TCPOS', function () {
    Http::fake([
        '*/getArticles' => Http::response([
            'getArticles' => [
                'articleList' => [
                    ['id' => 1, 'description' => 'Produit Test']
                ]
            ]
        ], 200),
    ]);

    // On teste la méthode via une route qui l'appelle ou directement si possible
    // Ici on va tester la route de consultation brute si elle existe
    $response = $this->getJson('/api/products/raw');

    $response->assertStatus(200)
        ->assertJsonFragment(['description' => 'Produit Test']);
});
