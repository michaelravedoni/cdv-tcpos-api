<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AttributeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/products/raw', [ProductController::class, 'getProducts'])->name('products.raw');
Route::get('/products/import', [ProductController::class, 'importProducts'])->name('products.import');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/prices', [ProductController::class, 'indexPrices'])->name('products.prices.index');
Route::get('/products/prices/import', [ProductController::class, 'importPrices'])->name('products.prices.import');
Route::get('/products/{id}/price', [ProductController::class, 'getPrice'])->name('products.show.price');
Route::get('/products/{id}/show', [ProductController::class, 'show'])->name('products.show');

Route::get('/products/images/import', [ProductController::class, 'importImages'])->name('products.images.import');

Route::get('/attributes/raw', [AttributeController::class, 'getAttributes'])->name('attributes.raw');
Route::get('/attributes/import', [AttributeController::class, 'importAttributes'])->name('attributes.import');
Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
Route::get('/attributes/{id}/show', [AttributeController::class, 'show'])->name('attributes.show');