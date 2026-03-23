<?php

use Codexshaper\WooCommerce\Facades\Order as WooOrder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SyncOrderUpdate;
use App\Http\Controllers\Sync\OrderController;

test('le paiement utilise le total TCPOS en cas d\'écart d\'arrondi avec WooCommerce', function () {
    Queue::fake();
    
    // WooCommerce total is 1387.70
    $wooOrder = (object)[
        'id' => 71172,
        'status' => 'processing',
        'total' => 1387.70,
        'payment_method' => 'PayEngine3Cw_CreditCard',
        'meta_data' => [],
        'coupon_lines' => [],
        'line_items' => [
            (object)['tcpos_id' => 1, 'quantity' => 1, 'price' => 1387.70]
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
        '*/createOrder*' => Http::response([
            'createOrder' => [
                'result' => 'OK',
                'data' => [
                    'guid' => 'TCPOS-GUID-71172',
                    'total' => 1387.68 // TCPOS total is slightly different
                ]
            ]
        ], 200),
        '*/confirmOrder*' => Http::response(['confirmOrder' => ['result' => 'OK']], 200),
    ]);

    $controller = new OrderController();
    $controller->sync();

    // Verify confirmOrder was called with the TCPOS total (1387.68) and NOT the WooCommerce total (1387.70)
    Http::assertSent(function ($request) {
        if (str_contains($request->url(), 'confirmOrder')) {
            $payments = json_decode($request->offsetGet('payments'), true);
            return $payments['payments'][0]['paymentAmount'] == 1387.68;
        }
        return true;
    });

    Queue::assertPushed(SyncOrderUpdate::class);
});

test('le paiement utilise le total TCPOS ajusté avec les fonds client en cas d\'écart d\'arrondi', function () {
    Queue::fake();
    
    // WooCommerce total is 1387.70, and 100.00 is already paid by funds
    $wooOrder = (object)[
        'id' => 71173,
        'status' => 'processing',
        'total' => 1387.70,
        'payment_method' => 'PayEngine3Cw_CreditCard',
        'meta_data' => [
            (object)['key' => '_funds_used', 'value' => 100.00]
        ],
        'coupon_lines' => [],
        'line_items' => [
            (object)['tcpos_id' => 1, 'quantity' => 1, 'price' => 1387.70]
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
        '*/createOrder*' => Http::response([
            'createOrder' => [
                'result' => 'OK',
                'data' => [
                    'guid' => 'TCPOS-GUID-71173',
                    'total' => 1387.68 // TCPOS total is 1387.68
                ]
            ]
        ], 200),
        '*/confirmOrder*' => Http::response(['confirmOrder' => ['result' => 'OK']], 200),
    ]);

    $controller = new OrderController();
    $controller->sync();

    // Verify confirmOrder:
    // Main payment should be 1387.68 (TCPOS total) - 100.00 (funds) = 1287.68
    // Second payment should be 100.00 (funds)
    Http::assertSent(function ($request) {
        if (str_contains($request->url(), 'confirmOrder')) {
            $payments = json_decode($request->offsetGet('payments'), true);
            return $payments['payments'][0]['paymentAmount'] == 1287.68 &&
                   $payments['payments'][1]['paymentAmount'] == 100.00;
        }
        return true;
    });
});
