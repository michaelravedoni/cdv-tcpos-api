<?php

use Spatie\Health\Facades\Health;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;

test('AppServiceProvider enregistre les services de base', function () {
    // On vérifie que JsonResource ne contient pas de "wrapping"
    // (C'est une vérification indirecte du boot de AppServiceProvider)
    expect(\Illuminate\Http\Resources\Json\JsonResource::$wrap)->toBeNull();
});

test('CheckServiceProvider enregistre les health checks', function () {
    $checks = collect(Health::registeredChecks());

    expect($checks->contains(fn($check) => $checks->first() instanceof DatabaseCheck))->toBeTrue();
    expect($checks->contains(fn($check) => $checks->last() instanceof EnvironmentCheck))->toBeTrue();
});
