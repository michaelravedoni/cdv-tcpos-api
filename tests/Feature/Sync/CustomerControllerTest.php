<?php

use App\Jobs\SyncCustomerUpdate;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;

test('importWooCustomers stocke les clients WooCommerce en local', function () {
    Customer::shouldReceive('all')
        ->times(7) // Le contrôleur fait 7 appels
        ->andReturn(collect([(object)['id' => 100, 'first_name' => 'Alice', 'sku' => 'SKU']]));

    $controller = new \App\Http\Controllers\Sync\CustomerController();
    $controller->importWooCustomers();

    $this->assertDatabaseHas('woos', [
        'resource' => 'customer',
        '_wooId' => 100
    ]);
});

test('sync depeche le job de mise a jour pour les clients avec carte', function () {
    Queue::fake();
    
    $customerData = (object)[
        'id' => 100,
        'card_number' => 'CARD123',
        'meta_data' => []
    ];
    
    Woo::create([
        'resource' => 'customer',
        '_wooId' => 100,
        'data' => $customerData
    ]);

    Order::shouldReceive('where')->andReturn(new class {
        function get() { return collect(); }
    });

    Http::fake([
        '*/login*' => Http::response(['login' => ['customerProperties' => ['token' => 't']]], 200),
        '*/searchCustomer*' => Http::response(['searchCustomerByData' => ['id' => 1]], 200),
        '*/getCustomer*' => Http::response(['getCustomer' => ['customer' => ['accountType' => 'customer', 'prepayBalanceCash' => 50, 'ID' => 1, 'firstName' => 'A', 'lastName' => 'B', 'cardNum' => 'CARD123']]], 200),
    ]);

    $controller = new \App\Http\Controllers\Sync\CustomerController();
    $controller->sync();

    Queue::assertPushed(SyncCustomerUpdate::class);
});
