<?php

use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\TcposController;
use App\Http\Controllers\Sync\SyncController;
use App\Http\Controllers\Sync\OrderController;
use Mockery\MockInterface;

it('runs import:tcpos command successfully', function () {
    $this->mock(ImportController::class, function (MockInterface $mock) {
        $mock->shouldReceive('importTcposAll')->once();
    });

    $this->artisan('import:tcpos')
        ->expectsOutput('Importation lauched')
        ->expectsOutput('Importation done. There are maybe queued jobs launched.')
        ->assertExitCode(0);
});

it('runs import:tcpos_articles command successfully', function () {
    $this->mock(TcposController::class, function (MockInterface $mock) {
        $mock->shouldReceive('importArticles')->once();
    });

    $this->artisan('import:tcpos_articles')
        ->expectsOutput('Importation lauched')
        ->expectsOutput('Importation done. There are maybe queued jobs launched.')
        ->assertExitCode(0);
});

it('runs import:woo command successfully', function () {
    $this->mock(ImportController::class, function (MockInterface $mock) {
        $mock->shouldReceive('importWooAll')->once();
    });

    $this->artisan('import:woo')
        ->expectsOutput('Importation lauched')
        ->expectsOutput('Importation done. There are maybe queued jobs launched.')
        ->assertExitCode(0);
});

it('runs sync:tcpos_woo command successfully', function () {
    $this->mock(SyncController::class, function (MockInterface $mock) {
        $mock->shouldReceive('all')->once();
    });

    $this->artisan('sync:tcpos_woo')
        ->expectsOutput('Synchronisation lauched')
        ->expectsOutput('Synchronisation done. There are queued jobs launched.')
        ->assertExitCode(0);
});

it('runs sync:tcpos_woo_order command successfully', function () {
    $this->mock(OrderController::class, function (MockInterface $mock) {
        $mock->shouldReceive('sync')->once();
    });

    $this->artisan('sync:tcpos_woo_order')
        ->expectsOutput('Synchronisation lauched')
        ->expectsOutput('Synchronisation done. There are queued jobs launched.')
        ->assertExitCode(0);
});
