<?php

use App\Jobs\SyncAttributeTermUpdate;
use App\Jobs\SyncAttributeUpdate;
use App\Models\Attribute as TcposAttribute;
use Codexshaper\WooCommerce\Facades\Attribute;
use Codexshaper\WooCommerce\Facades\Term;
use Illuminate\Support\Facades\Queue;

test('le controleur d\'attributs peut recuperer les attributs WooCommerce', function () {
    Attribute::shouldReceive('all')
        ->once()
        ->with(['per_page' => 100, 'page' => 1])
        ->andReturn(collect([(object)['id' => 1, 'name' => 'Color']]));

    $controller = new \App\Http\Controllers\Sync\AttributeController();
    $attributes = $controller->getWooAttributes();

    expect($attributes)->toHaveCount(1);
});

test('la synchronisation des attributs depeche les jobs nécessaires', function () {
    Queue::fake();

    Attribute::shouldReceive('all')->andReturn(collect([(object)['id' => 10, 'has_archives' => false]]));
    Term::shouldReceive('all')->andReturn(collect([(object)['id' => 20, 'name' => 'Test Term', 'meta_data' => []]]));

    TcposAttribute::factory()->create(['name' => 'Test Term', 'notes1' => 'url', 'notes2' => 'email', 'notes3' => 'phone']);

    $response = $this->getJson('/api/sync/attributes');

    $response->assertStatus(200);

    Queue::assertPushed(SyncAttributeUpdate::class);
    Queue::assertPushed(SyncAttributeTermUpdate::class);
});

test('la synchronisation des attributs ignore les elements inchanges', function () {
    Queue::fake();

    Attribute::shouldReceive('all')->andReturn(collect([(object)['id' => 10, 'has_archives' => true]]));
    Term::shouldReceive('all')->andReturn(collect([
        (object)[
            'id' => 20,
            'name' => 'Test Term',
            'meta_data' => [
                (object)['key' => 'website', 'value' => 'url'],
                (object)['key' => 'email', 'value' => 'email'],
                (object)['key' => 'phone', 'value' => 'phone'],
            ],
        ],
    ]));

    TcposAttribute::factory()->create(['name' => 'Test Term', 'notes1' => 'url', 'notes2' => 'email', 'notes3' => 'phone']);

    $response = $this->getJson('/api/sync/attributes');

    $response->assertStatus(200)
        ->assertJson([
            'count_attribute_update' => 0,
            'count_term_update' => 0,
            'count_term_unchanged' => 1,
        ]);

    Queue::assertNotPushed(SyncAttributeUpdate::class);
    Queue::assertNotPushed(SyncAttributeTermUpdate::class);
});
