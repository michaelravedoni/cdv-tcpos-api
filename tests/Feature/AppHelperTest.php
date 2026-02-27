<?php

use App\Utilities\AppHelper;
use Illuminate\Support\Facades\Http;
use anlutro\LaravelSettings\Facade as Setting;
use Codexshaper\WooCommerce\Facades\Order;
use Carbon\Carbon;

test('getLastTcposUpdate récupère correctement le timestamp depuis l\'API', function () {
    Http::fake([
        '*' => Http::response([
            'getLastRefreshTimestamp' => ['timestamp' => '2026-02-27 10:00:00']
        ], 200),
    ]);

    $result = AppHelper::getLastTcposUpdate();

    expect($result)->toBeInstanceOf(Carbon::class);
    expect($result->format('Y-m-d H:i:s'))->toBe('2026-02-27 10:00:00');
});

test('needImportFromTcpos retourne vrai si l\'API est plus récente que le local', function () {
    Http::fake([
        '*' => Http::response([
            'getLastRefreshTimestamp' => ['timestamp' => '2026-02-27 12:00:00']
        ], 200),
    ]);

    Setting::shouldReceive('get')
        ->with('lastTcposUpdate', null)
        ->andReturn('2026-02-27 10:00:00');

    expect(AppHelper::needImportFromTcpos())->toBeTrue();
});

test('needImportFromTcpos retourne faux si le local est à jour', function () {
    Http::fake([
        '*' => Http::response([
            'getLastRefreshTimestamp' => ['timestamp' => '2026-02-27 10:00:00']
        ], 200),
    ]);

    // Le helper utilise toDateTimeLocalString() qui produit un format avec un 'T'
    Setting::shouldReceive('get')
        ->with('lastTcposUpdate', null)
        ->andReturn('2026-02-27T11:00:00');

    expect(AppHelper::needImportFromTcpos())->toBeFalse();
});

test('getMetadataValueFromKey extrait la bonne valeur', function () {
    $metadata = [
        ['key' => 'color', 'value' => 'red'],
        ['key' => 'size', 'value' => 'XL'],
    ];

    expect(AppHelper::getMetadataValueFromKey($metadata, 'size'))->toBe('XL');
    expect(AppHelper::getMetadataValueFromKey($metadata, 'non-existent'))->toBeNull();
});
