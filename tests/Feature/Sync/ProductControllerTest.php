<?php

use App\Jobs\SyncProductCreate;
use App\Jobs\SyncProductUpdate;
use App\Models\Product as TcposProduct;
use App\Models\Price;
use App\Models\Woo;
use App\Models\ProductImage as TcposProductImage;
use Codexshaper\WooCommerce\Facades\Product as WooProduct;
use Illuminate\Support\Facades\Queue;

test('sync cree un produit WooCommerce s\'il n\'existe pas en local', function () {
    Queue::fake();
    config(['cdv.categories.wine' => ['manage_stock' => false]]);
    
    $tcposProduct = TcposProduct::factory()->create(['_tcposId' => 888, '_tcposCode' => 'SKU_NEW', 'category' => 'wine']);
    
    $productImage = new TcposProductImage();
    $productImage->_tcpos_product_id = 888;
    $productImage->hash = null;
    $productImage->save();
    
    Woo::where('_tcposCode', 'SKU_NEW')->delete();
    
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

    // Création des prix avec les pricelevelid corrects (2, 6, 14)
    Price::create(['_tcpos_product_id' => 999, 'pricelevelid' => '2', 'price' => 10, 'sync_action' => 'none']);
    Price::create(['_tcpos_product_id' => 999, 'pricelevelid' => '6', 'price' => 15, 'sync_action' => 'none']);
    Price::create(['_tcpos_product_id' => 999, 'pricelevelid' => '14', 'price' => 20, 'sync_action' => 'none']);

    // Création d'une image (même sans hash pour simplifier)
    $productImage = new TcposProductImage();
    $productImage->_tcpos_product_id = 999;
    $productImage->hash = null;
    $productImage->save();

    $woo = new Woo();
    $woo->resource = 'product';
    $woo->_wooId = 500;
    $woo->_tcposCode = 'SKU123';
    $woo->data = (object)['id' => 500, 'images' => []];
    $woo->save();

    $controller = new \App\Http\Controllers\Sync\ProductController();
    $controller->sync();

    Queue::assertPushed(SyncProductUpdate::class);
});
