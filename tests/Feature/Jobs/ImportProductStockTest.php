<?php

use App\Jobs\ImportProductStock;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    putenv('TCPOS_API_CDV_URL=http://localhost');
    $_ENV['TCPOS_API_CDV_URL'] = 'http://localhost';
});

it('imports stock when it does not exist in local database', function () {
    $id = 123;
    Http::fake(['*/getarticlesstock/*' => Http::response(['STOCK' => 50], 200)]);

    (new ImportProductStock($id))->handle();

    $this->assertDatabaseHas('stocks', [
        '_tcpos_product_id' => $id,
        'value' => "50",
        'sync_action' => 'update'
    ]);
});

it('updates existing stock if value changed', function () {
    $id = 123;
    Stock::create([
        '_tcpos_product_id' => $id,
        'value' => "50",
        'sync_action' => 'none'
    ]);

    Http::fake(['*/getarticlesstock/*' => Http::response(['STOCK' => 60], 200)]);

    (new ImportProductStock($id))->handle();

    $this->assertDatabaseHas('stocks', [
        '_tcpos_product_id' => $id,
        'value' => "60",
        'sync_action' => 'update'
    ]);
});

it('sets sync_action to none if stock value is unchanged', function () {
    $id = 123;
    $stock = Stock::create([
        '_tcpos_product_id' => $id,
        'value' => "50",
        'sync_action' => 'update'
    ]);

    Http::fake(['*/getarticlesstock/*' => Http::response(['STOCK' => 50], 200)]);

    (new ImportProductStock($id))->handle();

    $stock->refresh();
    expect($stock->sync_action)->toBe('none');
})->skip();
