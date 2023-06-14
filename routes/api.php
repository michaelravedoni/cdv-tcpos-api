<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\ImportController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\CheckController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Api\V1\VoucherController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\InfoController;
use App\Http\Controllers\Api\V1\TcposController;
use App\Http\Controllers\Sync\AttributeController as SyncAttributeController;
use App\Http\Controllers\Sync\CustomerController as SyncCustomerController;
use App\Http\Controllers\Sync\ProductController as SyncProductController;
use App\Http\Controllers\Sync\OrderController as SyncOrderController;
use App\Http\Controllers\Sync\SyncController as SyncController;

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

Route::get('/info', [InfoController::class, 'api'])->name('info');

/* TCPOS */
Route::get('/import/all', [ImportController::class, 'importTcposAll'])->name('import.all');
Route::get('/import/products', [ProductController::class, 'importProducts'])->name('import.products');
Route::get('/import/prices', [ProductController::class, 'importPrices'])->name('import.prices');
Route::get('/import/products/images', [ProductController::class, 'importImages'])->name('import.products.images');
Route::get('/import/products/images/{id}', [ProductController::class, 'importImage'])->name('import.products.image');
Route::get('/import/attributes', [AttributeController::class, 'importAttributes'])->name('import.attributes');
Route::get('/import/stocks', [StockController::class, 'importStocks'])->name('import.attributes');
Route::get('/import/need/tcpos', [ImportController::class, 'needImportFromTcpos'])->name('import.need.tcpos');
Route::get('/import/need/woo/orders', [ImportController::class, 'needOrdersImportFromWoo'])->name('import.need.woo.orders');

Route::get('/products/raw', [ProductController::class, 'getProducts'])->name('products.raw');
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/prices', [ProductController::class, 'indexPrices'])->name('products.prices.index');
Route::get('/products/getById/{id}', [ProductController::class, 'getById'])->name('products.getById');
Route::get('/products/getByCode/{id}', [ProductController::class, 'getByCode'])->name('products.getByCode');
Route::get('/products/{id}/price', [ProductController::class, 'getPrice'])->name('products.show.price');
Route::get('/products/{category}', [ProductController::class, 'indexByCategory'])->name('products.category');

Route::get('/tcpos/raw', [TcposController::class, 'getDB'])->name('tcpos.raw');
Route::get('/tcpos/articles/raw', [TcposController::class, 'getArticles'])->name('tcpos.articles.raw');
Route::get('/tcpos/import/articles', [TcposController::class, 'importArticles'])->name('tcpos.import.articles');
Route::get('/tcpos/articles/wine', [TcposController::class, 'showWineMenu'])->name('tcpos.articles-wine')->middleware('cache.headers:public;max_age=3600');

Route::get('/attributes/raw', [AttributeController::class, 'getAttributes'])->name('attributes.raw');
Route::get('/attributes', [AttributeController::class, 'index'])->name('attributes.index');
Route::get('/attributes/{id}', [AttributeController::class, 'show'])->name('attributes.show');

Route::get('/stocks/{id}', [StockController::class, 'getStock'])->name('stocks.show');

Route::get('/vouchers', [VoucherController::class, 'getVouchers'])->name('vouchers.index');
Route::get('/vouchers/{id}', [VoucherController::class, 'getVoucher'])->name('vouchers.show');

Route::get('/customers/{cardnum}', [CustomerController::class, 'getCustomerByCardnum'])->name('customers.show');
Route::get('/customers/{cardnum}/funds', [CustomerController::class, 'getCustomerFundsByCardnumber'])->name('customers.funds');
Route::get('/customers/{cardnum}/verification', [CustomerController::class, 'getCustomerVerificationField'])->name('customers.verification');
Route::post('/customers/{cardnum}/verification', [CustomerController::class, 'verifyCustomer'])->name('customers.verification.post');

Route::post('/orders', [OrderController::class, 'postOrders'])->name('orders.post');

/* Woocommerce */
Route::get('/wc/import/all', [ImportController::class, 'importWooAll'])->name('wc.import.all');

Route::get('/wc/attributes', [SyncAttributeController::class, 'getWooAttributes'])->name('wc.attributes');
Route::get('/wc/attributes/cellar', [SyncAttributeController::class, 'getWooCellarTerms'])->name('wc.attributes.cellar');
//Route::get('/wc/customers', [SyncCustomerController::class, 'getWooCustomers'])->name('wc.customers');
Route::get('/wc/products', [SyncProductController::class, 'getWooProducts'])->name('wc.products');
Route::get('/wc/orders', [SyncOrderController::class, 'getWooOrders'])->name('wc.orders');

/* Sync */
Route::get('/sync/attributes', [SyncAttributeController::class, 'sync'])->name('wc.sync.attributes');
Route::get('/sync/customers', [SyncCustomerController::class, 'sync'])->name('wc.sync.customers');
Route::get('/sync/products', [SyncProductController::class, 'sync'])->name('wc.sync.products');
Route::get('/sync/orders', [SyncOrderController::class, 'sync'])->name('wc.sync.orders');
Route::get('/sync/all', [SyncController::class, 'all'])->name('wc.sync.all');

/* Check */
Route::get('/check/woo', [CheckController::class, 'woo'])->name('check.woo');
