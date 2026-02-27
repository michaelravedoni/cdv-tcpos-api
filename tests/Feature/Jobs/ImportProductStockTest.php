<?php

use App\Jobs\ImportProductStock;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    config(['cdv.tcpos.api_cdv_url' => 'http://localhost']);
    DB::table('stocks')->truncate();
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
    DB::table('stocks')->insert([
        '_tcpos_product_id' => $id,
        'value' => "50",
        'sync_action' => 'none',
        'created_at' => now(),
        'updated_at' => now(),
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
    DB::table('stocks')->insert([
        '_tcpos_product_id' => $id,
        'value' => "50",
        'sync_action' => 'update',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Http::fake(['*/getarticlesstock/*' => Http::response(['STOCK' => 50], 200)]);

    (new ImportProductStock($id))->handle();

    $stock = Stock::where('_tcpos_product_id', $id)->first();
    expect($stock->sync_action)->toBe('none');
});
