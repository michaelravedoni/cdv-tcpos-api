<?php

use App\Models\Attribute;
use Illuminate\Support\Facades\Http;

test('index retourne tous les attributs', function () {
    Attribute::factory()->count(3)->create();

    $response = $this->getJson('/api/attributes');

    $response->assertStatus(200)
        ->assertJsonCount(3);
});

test('show retourne un attribut par son ID TCPOS', function () {
    $attribute = Attribute::factory()->create(['_tcposId' => 123]);

    $response = $this->getJson('/api/attributes/123');

    $response->assertStatus(200)
        ->assertJsonFragment(['_tcposId' => 123]);
});

test('importAttributes importe les données depuis TCPOS', function () {
    Http::fake([
        '*/getallgroups' => Http::response([
            'GROUPS' => [
                [
                    'ID' => 1,
                    'CODE' => 'ATTR1',
                    'DESCRIPTION' => 'Test Attribute',
                    'NOTES1' => 'N1',
                    'NOTES2' => 'N2',
                    'NOTES3' => 'N3',
                ]
            ]
        ], 200),
    ]);

    $response = $this->getJson('/api/import/attributes');

    $response->assertStatus(200)
        ->assertJsonFragment(['message' => 'imported']);

    $this->assertDatabaseHas('attributes', [
        '_tcposId' => 1,
        'name' => 'Test Attribute'
    ]);
});
