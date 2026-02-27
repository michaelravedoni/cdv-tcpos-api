<?php

use App\Models\Woo;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    DB::table('products')->truncate();
    DB::table('product_images')->truncate();
    DB::table('woos')->truncate();
});

it('detects products missing images in WooCommerce and marks them for sync', function () {
    DB::table('products')->insert([
        'id' => 1,
        '_tcposId' => 123,
        'name' => 'Test Product',
        'category' => 'wine',
        'sync_action' => 'none',
    ]);

    DB::table('product_images')->insert([
        'id' => 1,
        '_tcpos_product_id' => 123,
        'hash' => 'fakehash',
        'sync_action' => 'none',
    ]);

    DB::table('woos')->insert([
        'id' => 1,
        '_wooId' => 456,
        '_tcposId' => 123,
        'resource' => 'product',
        'data' => json_encode([
            'id' => 456,
            'images' => [],
        ]),
    ]);

    $this->artisan('check:woo')
        ->expectsOutput('Check if everthing is ok with woocommerce')
        ->expectsOutput('TCPOS Ids that will be updated : 123')
        ->assertExitCode(0);

    $product = Product::where('_tcposId', 123)->first();
    expect($product->sync_action)->toBe('update');
});

it('does nothing if WooCommerce product already has an image', function () {
    DB::table('products')->insert([
        'id' => 2,
        '_tcposId' => 789,
        'name' => 'Product with Image',
        'category' => 'wine',
        'sync_action' => 'none',
    ]);

    DB::table('product_images')->insert([
        'id' => 2,
        '_tcpos_product_id' => 789,
        'hash' => 'fakehash',
        'sync_action' => 'none',
    ]);

    DB::table('woos')->insert([
        'id' => 2,
        '_wooId' => 101,
        '_tcposId' => 789,
        'resource' => 'product',
        'data' => json_encode([
            'id' => 101,
            'images' => [['src' => 'https://example.com/image.jpg']],
        ]),
    ]);

    $this->artisan('check:woo')
        ->expectsOutput('Check done.')
        ->assertExitCode(0);

    $product = Product::where('_tcposId', 789)->first();
    expect($product->sync_action)->toBe('none');
});
