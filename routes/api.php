<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\StockController;

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


Route::get('/import/products', [ProductController::class, 'importProducts'])->name('import.products');
Route::get('/import/products/prices', [ProductController::class, 'importPrices'])->name('import.products');
Route::get('/import/products/images', [ProductController::class, 'importImages'])->name('import.products.images');
Route::get('/import/attributes', [AttributeController::class, 'importAttributes'])->name('import.attributes');
Route::get('/import/stocks', [StockController::class, 'importStocks'])->name('import.attributes');

Route::get('/products/raw', [ProductController::class, 'getProducts'])->name('products.raw');
Route::get('/products/import', [ProductController::class, 'importProducts'])->name('products.import');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/prices', [ProductController::class, 'indexPrices'])->name('products.prices.index');
Route::get('/products/{id}/price', [ProductController::class, 'getPrice'])->name('products.show.price');
Route::get('/products/{id}/show', [ProductController::class, 'show'])->name('products.show');

Route::get('/attributes/raw', [AttributeController::class, 'getAttributes'])->name('attributes.raw');
Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
Route::get('/attributes/{id}/show', [AttributeController::class, 'show'])->name('attributes.show');

Route::get('/stocks/{id}/show', [StockController::class, 'getStock'])->name('stocks.show');