<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AttributeController;
use App\Http\Controllers\Api\V1\StockController;

class ImportController extends Controller
{
    /**
     * Import all.
     */
    public function importAll()
    {
        $begin = microtime(true);

        $product_controller = new ProductController;
        $attribute_controller = new AttributeController;
        $stock_controller = new StockController;

        $product_controller_return = $product_controller->importProducts();
        $attribute_controller_return = $attribute_controller->importAttributes();
        $stock_controller_return = $stock_controller->importStocks();
        $product_controller_prices_return = $product_controller->importPrices();

        $end = microtime(true) - $begin;

        return response()->json([
            'message' => 'Import launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                'products' => $product_controller_return->original,
                'attributes' => $attribute_controller_return->original,
                'stocks' => $stock_controller_return->original,
                'prices' => $product_controller_prices_return->original,
            ],
        ]);
    }
}
