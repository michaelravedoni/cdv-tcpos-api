<?php

use App\Http\Controllers\ViewController;
use Illuminate\Support\Facades\Route;
use Spatie\Health\Http\Controllers\HealthCheckResultsController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
Route::get('/', function () {
    return view('welcome');
});
*/
Route::get('/', [ViewController::class, 'show'])->name('info');
Route::get('/tables', [ViewController::class, 'tables'])->name('tables');
Route::post('/tables/products/{id}/force-update', [ViewController::class, 'forceUpdateProduct'])->name('tables.products.force.update');

Route::prefix('jobs')->group(function () {
    Route::queueMonitor();
});
Route::get('health', HealthCheckResultsController::class);
