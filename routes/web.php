<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\InfoController;

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
Route::get('/', [InfoController::class, 'show'])->name('info');
Route::get('/tables', [InfoController::class, 'tables'])->name('tables');

Route::prefix('jobs')->group(function () {
    Route::queueMonitor();
});
