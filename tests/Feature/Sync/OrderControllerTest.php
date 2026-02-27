<?php

use Codexshaper\WooCommerce\Facades\Order as WooOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncOrderUpdate;

test('sync cree des commandes dans TCPOS pour les commandes en traitement', function () {
    Queue::fake();
    
    $wooOrder = (object)[
        'id' => 1001,
        'status' => 'processing',
        'meta_data' => [],
        'coupon_lines' => [],
        'line_items' => [
            (object)['tcpos_id' => 1, 'quantity' => 1, 'price' => 10.0]
        ],
        'shipping' => (object)[
            'first_name' => 'Jean',
            'last_name' => 'Dupont',
            'address_1' => 'Rue du Lac 1',
            'postcode' => '1950',
            'city' => 'Sion',
            'country' => 'CH'
        ],
        'customer_id' => 0
    ];

    WooOrder::shouldReceive('all')->andReturn(collect([$wooOrder]));

    Http::fake([
        '*/login*' => Http::response(['login' => ['customerProperties' => ['token' => 'test-token']]], 200),
        '*/createOrder*' => Http::response(['createOrder' => ['result' => 'OK', 'data' => ['guid' => 'TCPOS-GUID']]], 200),
        '*/confirmOrder*' => Http::response(['confirmOrder' => ['result' => 'OK']], 200),
    ]);

    $controller = new \App\Http\Controllers\Sync\OrderController();
    $controller->sync();

    Queue::assertPushed(SyncOrderUpdate::class);
});
