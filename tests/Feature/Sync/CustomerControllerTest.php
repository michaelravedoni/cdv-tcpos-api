<?php

use App\Jobs\SyncCustomerUpdate;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Http;

test('importWooCustomers stocke les clients WooCommerce en local avec pagination dynamique', function () {
    $page1 = collect(range(1, 100))->map(fn ($i) => (object) ['id' => $i, 'first_name' => "User{$i}"]);
    $page2 = collect([(object) ['id' => 101, 'first_name' => 'Alice']]);

    Customer::shouldReceive('all')
        ->once()
        ->with(['per_page' => 100, 'page' => 1, 'has_card' => 1])
        ->andReturn($page1);

    Customer::shouldReceive('all')
        ->once()
        ->with(['per_page' => 100, 'page' => 2, 'has_card' => 1])
        ->andReturn($page2);

    $controller = new \App\Http\Controllers\Sync\CustomerController();
    $response = $controller->importWooCustomers();

    expect($response->getData(true)['count'])->toBe(101);
    $this->assertDatabaseHas('woos', [
        'resource' => 'customer',
        '_wooId' => 101,
    ]);
});

test('sync depeche le job de mise a jour pour les clients avec carte quand le solde change', function () {
    Queue::fake();

    $customerData = (object) [
        'id' => 100,
        'card_number' => '20018',
        'account_funds' => 10,
        'meta_data' => [
            (object) ['key' => 'account_type', 'value' => 'customer'],
        ],
    ];

    Woo::create([
        'resource' => 'customer',
        '_wooId' => 100,
        'data' => $customerData,
    ]);

    Order::shouldReceive('where')->andReturn(new class {
        public function get() { return collect(); }
    });

    Http::fake([
        '*/login*' => Http::response(['login' => ['customerProperties' => ['token' => 't']]], 200),
        '*/searchCustomer*' => Http::response(['searchCustomerByData' => ['id' => 1]], 200),
        '*/getCustomer*' => Http::response(['getCustomer' => ['customer' => ['prepayBalanceCash' => 50, 'ID' => 1, 'firstName' => 'A', 'lastName' => 'B', 'cardNum' => '20018']]], 200),
    ]);

    $controller = new \App\Http\Controllers\Sync\CustomerController();
    $controller->sync();

    Queue::assertPushed(SyncCustomerUpdate::class, function ($job) {
        return $job->id === 100
            && $job->data['account_funds'] === 50.0
            && $job->data['meta_data'] === [['key' => 'account_type', 'value' => 'customer']];
    });
});

test('sync ne depeche pas de job si le solde et le type sont identiques', function () {
    Queue::fake();

    Woo::create([
        'resource' => 'customer',
        '_wooId' => 101,
        'data' => (object) [
            'id' => 101,
            'card_number' => '20019',
            'account_funds' => 50,
            'meta_data' => [
                (object) ['key' => 'account_type', 'value' => 'customer'],
            ],
        ],
    ]);

    Order::shouldReceive('where')->andReturn(new class {
        public function get() { return collect(); }
    });

    Http::fake([
        '*/login*' => Http::response(['login' => ['customerProperties' => ['token' => 't']]], 200),
        '*/searchCustomer*' => Http::response(['searchCustomerByData' => ['id' => 1]], 200),
        '*/getCustomer*' => Http::response(['getCustomer' => ['customer' => ['prepayBalanceCash' => 50, 'ID' => 1, 'firstName' => 'A', 'lastName' => 'B', 'cardNum' => '20019']]], 200),
    ]);

    $controller = new \App\Http\Controllers\Sync\CustomerController();
    $response = $controller->sync();

    expect($response->getData(true)['count_customer_unchanged'])->toBe(1);
    Queue::assertNotPushed(SyncCustomerUpdate::class);
});

test('sync ignore les fausses cartes non numeriques sans appeler TCPOS', function () {
    Queue::fake();
    Http::fake();

    Woo::create([
        'resource' => 'customer',
        '_wooId' => 102,
        'data' => (object) [
            'id' => 102,
            'card_number' => 'syjzvgwxnw',
            'account_funds' => 0,
            'meta_data' => [],
        ],
    ]);

    $controller = new \App\Http\Controllers\Sync\CustomerController();
    $response = $controller->sync();

    expect($response->getData(true)['count_customer_no_card'])->toBe(1);
    Http::assertNothingSent();
    Queue::assertNotPushed(SyncCustomerUpdate::class);
});
