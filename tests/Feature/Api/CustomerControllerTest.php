<?php

use Illuminate\Support\Facades\Http;

test('getCustomer récupère un client via TCPOS', function () {
    Http::fake([
        '*/login*' => Http::response(['login' => ['customerProperties' => ['token' => 'test-token']]], 200),
        '*/searchCustomerByData*' => Http::response(['searchCustomerByData' => ['id' => 456]], 200),
        '*/getCustomer*' => Http::response([
            'getCustomer' => [
                'customer' => [
                    'ID' => 456,
                    'firstName' => 'Jean',
                    'lastName' => 'Dupont',
                    'email' => 'jean@example.com',
                    'zipCode' => '1950',
                    'cardNum' => '123456'
                ]
            ]
        ], 200),
    ]);

    $response = $this->withHeaders(['X-Header-Name' => env('TCPOS_API_SECRET')])
        ->getJson('/api/customers/123456');

    $response->assertStatus(200)
        ->assertJsonFragment(['firstName' => 'Jean', 'zipcode' => '1950']);
});

test('verifyCustomer valide correctement le code postal', function () {
    Http::fake([
        '*/login*' => Http::response(['login' => ['customerProperties' => ['token' => 'test-token']]], 200),
        '*/searchCustomerByData*' => Http::response(['searchCustomerByData' => ['id' => 456]], 200),
        '*/getCustomer*' => Http::response([
            'getCustomer' => [
                'customer' => [
                    'ID' => 456,
                    'zipCode' => '1950'
                ]
            ]
        ], 200),
    ]);

    // Cas valide
    $response = $this->withHeaders(['X-Header-Name' => env('TCPOS_API_SECRET')])
        ->postJson('/api/customers/123456/verification', [
            'verificationFields' => [['value' => '1950']]
        ]);
    $response->assertStatus(200);

    // Cas invalide
    $response = $this->withHeaders(['X-Header-Name' => env('TCPOS_API_SECRET')])
        ->postJson('/api/customers/123456/verification', [
            'verificationFields' => [['value' => '9999']]
        ]);
    $response->assertStatus(401);
});
