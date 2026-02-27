<?php

use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', [ViewController::class, 'show'])->name('info');
Route::get('/tables', [ViewController::class, 'tables'])->name('tables');
Route::post('/tables/products/{id}/force-update', [ViewController::class, 'forceUpdateProduct'])->name('tables.products.force.update');

Route::get('health', HealthCheckResultsController::class);
