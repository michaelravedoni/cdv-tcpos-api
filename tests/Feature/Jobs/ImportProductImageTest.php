<?php

use App\Jobs\ImportProductImage;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    
    config([
        'cdv.tcpos.api_wond_url' => 'http://localhost',
        'cdv.tcpos.products_images_base_path' => 'images',
    ]);
});

it('imports a product image when it does not exist in local database', function () {
    $id = 123;
    $imageData = base64_encode('test-image-content');
    $response = [
        'getImage' => [
            'imageList' => [
                [
                    'bitmapFile' => $imageData
                ]
            ]
        ]
    ];

    Http::fake([
        '*/getImage*' => Http::response($response, 200)
    ]);

    (new ImportProductImage($id))->handle();

    $this->assertDatabaseHas('product_images', [
        '_tcpos_product_id' => $id,
        'hash' => md5($imageData),
        'sync_action' => 'update'
    ]);

    Storage::disk('public')->assertExists('images/' . $id . '.jpg');
});

it('updates an existing product image if hash is different', function () {
    $id = 123;
    $oldImageData = base64_encode('old-content');
    $newImageData = base64_encode('new-content');
    
    ProductImage::create([
        '_tcpos_product_id' => $id,
        'hash' => md5($oldImageData),
        'sync_action' => 'none'
    ]);

    Http::fake([
        '*/getImage*' => Http::response([
            'getImage' => [
                'imageList' => [['bitmapFile' => $newImageData]]
            ]
        ], 200)
    ]);

    (new ImportProductImage($id))->handle();

    $this->assertDatabaseHas('product_images', [
        '_tcpos_product_id' => $id,
        'hash' => md5($newImageData),
        'sync_action' => 'update'
    ]);
});

it('does nothing when the image hash is the same in tcpos and local database', function () {
    $id = 123;
    $imageData = base64_encode('same-content');
    $hash = md5($imageData);

    $productImage = new ProductImage();
    $productImage->_tcpos_product_id = $id;
    $productImage->hash = $hash;
    $productImage->sync_action = 'update';
    $productImage->save();

    Http::fake([
        '*/getImage*' => Http::response([
            'getImage' => [
                'imageList' => [['bitmapFile' => $imageData]]
            ]
        ], 200)
    ]);

    (new ImportProductImage($id))->handle();

    $productImage->refresh();
    expect($productImage->sync_action)->toBe('none');
});

it('resets hash and labels no sync action if no image found in TCPOS', function () {
    $id = 123;
    ProductImage::create([
        '_tcpos_product_id' => $id,
        'hash' => 'somehash',
        'sync_action' => 'update'
    ]);

    Http::fake([
        '*/getImage*' => Http::response([
            'getImage' => [
                'imageList' => []
            ]
        ], 200)
    ]);

    (new ImportProductImage($id))->handle();

    $this->assertDatabaseHas('product_images', [
        '_tcpos_product_id' => $id,
        'hash' => null,
        'sync_action' => 'none'
    ]);
});
