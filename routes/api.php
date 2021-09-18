<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\VoucherController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\InfoController;
use App\Http\Controllers\Sync\AttributeController as SyncAttributeController;
use App\Http\Controllers\Sync\CustomerController as SyncCustomerController;

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


Route::get('/import/all', [ImportController::class, 'importAll'])->name('import.all');
Route::get('/import/products', [ProductController::class, 'importProducts'])->name('import.products');
Route::get('/import/prices', [ProductController::class, 'importPrices'])->name('import.prices');
Route::get('/import/products/images', [ProductController::class, 'importImages'])->name('import.products.images');
Route::get('/import/products/images/{id}', [ProductController::class, 'importImage'])->name('import.products.image');
Route::get('/import/attributes', [AttributeController::class, 'importAttributes'])->name('import.attributes');
Route::get('/import/stocks', [StockController::class, 'importStocks'])->name('import.attributes');

Route::get('/products/raw', [ProductController::class, 'getProducts'])->name('products.raw');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/prices', [ProductController::class, 'indexPrices'])->name('products.prices.index');
Route::get('/products/getById/{id}', [ProductController::class, 'show'])->name('products.getById');
Route::get('/products/menu', [ProductController::class, 'indexByCategory'])->name('products.category');
Route::get('/products/{id}/price', [ProductController::class, 'getPrice'])->name('products.show.price');
Route::get('/products/{id}/show', [ProductController::class, 'show'])->name('products.show');
Route::get('/products/{category}', [ProductController::class, 'indexByCategory'])->name('products.category');

Route::get('/attributes/raw', [AttributeController::class, 'getAttributes'])->name('attributes.raw');
Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
Route::get('/attributes/{id}/show', [AttributeController::class, 'show'])->name('attributes.show');

Route::get('/stocks/{id}/show', [StockController::class, 'getStock'])->name('stocks.show');

Route::get('/vouchers', [VoucherController::class, 'getVouchers'])->name('vouchers.index');
Route::get('/vouchers/{id}', [VoucherController::class, 'getVoucher'])->name('vouchers.show');

Route::get('/customers/{cardnum}', [CustomerController::class, 'getCustomerByCardnum'])->name('customers.show');
Route::get('/customers/{cardnum}/funds', [CustomerController::class, 'getCustomerFundsByCardnumber'])->name('customers.funds');
Route::get('/customers/{cardnum}/verification', [CustomerController::class, 'getCustomerVerificationField'])->name('customers.verification');
Route::post('/customers/{cardnum}/verification', [CustomerController::class, 'verifyCustomer'])->name('customers.verification.post');

Route::post('/orders', [OrderController::class, 'postOrders'])->name('orders.post');

Route::get('/info', [InfoController::class, 'show'])->name('info');

/* Woocommerce sync */
Route::get('/wc/attributes', [SyncAttributeController::class, 'getWooAttributes'])->name('wc.attributes');
Route::get('/wc/attributes/cellar', [SyncAttributeController::class, 'getWooCellarTerms'])->name('wc.attributes.cellar');
Route::get('/wc/customers', [SyncCustomerController::class, 'getWooCustomers'])->name('wc.customers');

Route::get('/wc/sync/attributes', [SyncAttributeController::class, 'sync'])->name('wc.sync.attributes');
Route::get('/wc/sync/customers', [SyncCustomerController::class, 'sync'])->name('wc.sync.customers');