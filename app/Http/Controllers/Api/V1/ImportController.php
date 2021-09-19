<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\StockController;
use App\Http\Controllers\Sync\ProductController as SyncProductController;
use App\Http\Controllers\Sync\CustomerController as SyncCustomerController;

class ImportController extends Controller
{
    /**
     * Import tcpos all.
     */
    public function importTcposAll()
    {
        $begin = microtime(true);

        activity()->log('Import: --START-- Import all from tcpos database');

        $product_controller = new ProductController;
        $attribute_controller = new AttributeController;
        $stock_controller = new StockController;

        $product_controller_return = $product_controller->importProducts();
        $attribute_controller_return = $attribute_controller->importAttributes();
        $stock_controller_return = $stock_controller->importStocks();
        $product_controller_prices_return = $product_controller->importPrices();

        $end = microtime(true) - $begin;

        activity()->withProperties(['duration' => $end])->log('Import: --END-- All imported from tcpos database | See /jobs for all importations');

        return response()->json([
            'message' => 'Tcpos Import launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                'products' => $product_controller_return->original,
                'attributes' => $attribute_controller_return->original,
                'stocks' => $stock_controller_return->original,
                'prices' => $product_controller_prices_return->original,
            ],
        ]);
    }

    /**
     * Import woo all.
     */
    public function importWooAll()
    {
        $begin = microtime(true);

        activity()->log('Import: --START-- Import all from Woocommerce database');

        $sync_product_controller = new SyncProductController;
        $sync_customer_controller = new SyncCustomerController;
        $sync_product_controller_return = $sync_product_controller->importWooProducts();
        $sync_customer_controller_return = $sync_customer_controller->importWooCustomers();

        $end = microtime(true) - $begin;

        activity()->withProperties(['duration' => $end])->log('Import: --END -- All imported from Woocommerce database');
        
        return response()->json([
            'message' => 'Woo Import launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                'products' => $sync_product_controller_return->original,
                'customers' => $sync_customer_controller_return->original,
            ],
        ]);
    }
}
