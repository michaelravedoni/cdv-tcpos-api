<?php

use App\Jobs\ImportProductPrice;
use App\Models\Price;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    putenv('TCPOS_API_WOND_URL=http://localhost');
    $_ENV['TCPOS_API_WOND_URL'] = 'http://localhost';
});

it('imports prices when they do not exist in local database', function () {
    $id = 123;
    $response = [
        'getPrice' => [
            'data' => [
                'itemList' => [
                    [
                        'article' => [
                            'id' => $id,
                            'price' => 10.5,
                            'discountedprice' => 9.5,
                            'pricelevelid' => 14
                        ]
                    ]
                ]
            ]
        ]
    ];

    Http::fake([
        '*/getPrice*' => Http::response($response, 200)
    ]);

    (new ImportProductPrice($id))->handle();

    $this->assertDatabaseHas('prices', [
        '_tcpos_product_id' => $id,
        'price' => 10.5,
        'pricelevelid' => 14,
        'sync_action' => 'update'
    ]);
});

it('updates existing prices if price changed', function () {
    $id = 123;
    Price::create([
        '_tcpos_product_id' => $id,
        'price' => 10.0,
        'discountedprice' => 9.0,
        'pricelevelid' => 14,
        'sync_action' => 'none'
    ]);

    Http::fake([
        '*/getPrice*' => Http::response([
            'getPrice' => [
                'data' => [
                    'itemList' => [
                        [
                            'article' => [
                                'id' => $id,
                                'price' => 11.0,
                                'discountedprice' => 10.0,
                                'pricelevelid' => 14
                            ]
                        ]
                    ]
                ]
            ]
        ], 200)
    ]);

    (new ImportProductPrice($id))->handle();

    $this->assertDatabaseHas('prices', [
        '_tcpos_product_id' => $id,
        'price' => 11.0,
        'sync_action' => 'update'
    ]);
});

it('sets sync_action to none if price is unchanged', function () {
    $id = 123;
    $price = Price::create([
        '_tcpos_product_id' => $id,
        'price' => 10.0,
        'discountedprice' => 9.0,
        'pricelevelid' => 14,
        'sync_action' => 'update'
    ]);

    Http::fake([
        '*/getPrice*' => Http::response([
            'getPrice' => [
                'data' => [
                    'itemList' => [
                        [
                            'article' => [
                                'id' => $id,
                                'price' => 10.0,
                                'discountedprice' => 9.0,
                                'pricelevelid' => 14
                            ]
                        ]
                    ]
                ]
            ]
        ], 200)
    ]);

    (new ImportProductPrice($id))->handle();

    $price->refresh();
    expect($price->sync_action)->toBe('none');
});
