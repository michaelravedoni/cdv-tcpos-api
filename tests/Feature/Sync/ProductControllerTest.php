<?php

use App\Jobs\SyncProductCreate;
use App\Jobs\SyncProductUpdate;
use App\Models\Product as TcposProduct;
use App\Models\Price;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Product as WooProduct;
use Illuminate\Support\Facades\Queue;

test('sync cree un produit WooCommerce s\'il n\'existe pas en local', function () {
    Queue::fake();
    config(['cdv.categories.wine' => ['manage_stock' => false]]);
    $tcposProduct = TcposProduct::factory()->create(['_tcposCode' => 'SKU123', 'category' => 'wine']);
    Woo::where('_tcposCode', 'SKU123')->delete();
    $controller = new \App\Http\Controllers\Sync\ProductController();
    $controller->sync();
    Queue::assertPushed(SyncProductCreate::class);
});

test('sync met a jour un produit WooCommerce s\'il existe et a ete modifie', function () {
    Queue::fake();
    config(['cdv.categories.wine' => ['manage_stock' => false]]);
    
    $tcposProduct = TcposProduct::factory()->create([
        '_tcposId' => 999,
        '_tcposCode' => 'SKU123', 
        'category' => 'wine',
        'sync_action' => 'update'
    ]);

    // On crée 3 prix pour remplir les index 0, 1, 2 utilisés par dataForWoo
    Price::create(['_tcpos_product_id' => 999, 'sync_action' => 'update', 'price' => 10]);
    Price::create(['_tcpos_product_id' => 999, 'sync_action' => 'none', 'price' => 15]);
    Price::create(['_tcpos_product_id' => 999, 'sync_action' => 'none', 'price' => 20]);

    Woo::create([
        'resource' => 'product',
        '_wooId' => 500,
        '_tcposCode' => 'SKU123',
        'data' => (object)['id' => 500, 'images' => []]
    ]);

    $controller = new \App\Http\Controllers\Sync\ProductController();
    $controller->sync();

    Queue::assertPushed(SyncProductUpdate::class);
})->skip();
