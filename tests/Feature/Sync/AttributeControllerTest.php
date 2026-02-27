<?php

use App\Jobs\SyncAttributeTermUpdate;
use App\Jobs\SyncAttributeUpdate;
use App\Models\Attribute as TcposAttribute;
use Codexshaper\WooCommerce\Facades\Attribute;
use Codexshaper\WooCommerce\Facades\Term;
use Illuminate\Support\Facades\Queue;

test('le controleur d\'attributs peut recuperer les attributs WooCommerce', function () {
    Attribute::shouldReceive('all')
        ->times(3)
        ->andReturn(collect([(object)['id' => 1, 'name' => 'Color']]));

    $controller = new \App\Http\Controllers\Sync\AttributeController();
    $attributes = $controller->getWooAttributes();

    // On s'attend à 3 car on merge 3 pages de 1 résultat
    expect($attributes)->toHaveCount(3);
});

test('la synchronisation des attributs depeche les jobs nécessaires', function () {
    Queue::fake();

    Attribute::shouldReceive('all')->andReturn(collect([(object)['id' => 10]]));
    Term::shouldReceive('all')->andReturn(collect([(object)['id' => 20, 'name' => 'Test Term']]));

    TcposAttribute::factory()->create(['name' => 'Test Term', 'notes1' => 'url', 'notes2' => 'email', 'notes3' => 'phone']);

    $response = $this->getJson('/api/sync/attributes');

    $response->assertStatus(200);

    Queue::assertPushed(SyncAttributeUpdate::class);
    Queue::assertPushed(SyncAttributeTermUpdate::class);
});
