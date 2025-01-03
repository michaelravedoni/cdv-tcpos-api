<?php

namespace App\Http\Controllers\Api\V1;

use App\Utilities\AppHelper;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Codexshaper\WooCommerce\Facades\Order;
use anlutro\LaravelSettings\Facade as Setting;
use App\Http\Controllers\Sync\ProductController as SyncProductController;
use App\Http\Controllers\Sync\CustomerController as SyncCustomerController;

class ImportController extends Controller
{
    /**
     * Import tcpos all.
     */
    public function importTcposAll(): JsonResponse
    {
        $begin = microtime(true);

        activity()->withProperties(['group' => 'import-woo', 'level' => 'start', 'resource' => 'all'])->log('---- Import from TCPOS ----');

        $force = request()->input('force', false);

        if (! AppHelper::needImportFromTcpos() && ! $force) {
            activity()->withProperties(['group' => 'import-tcpos', 'level' => 'end', 'resource' => 'all'])->log('No need to import data from tcpos. Last tcpos database update : '.Setting::get('lastTcposUpdate'));

            return response()->json([
                'message' => 'No need to import data from tcpos. Last tcpos database update : '.Setting::get('lastTcposUpdate'),
            ]);
        }

        $product_controller = new ProductController;
        $attribute_controller = new AttributeController;
        $stock_controller = new StockController;

        $product_controller_return = $product_controller->importProducts();
        $attribute_controller_return = $attribute_controller->importAttributes();
        $stock_controller_return = $stock_controller->importStocks();
        $product_controller_prices_return = $product_controller->importPrices();
        $product_controller_images_return = $product_controller->importImages();

        $end = microtime(true) - $begin;

        // Set last TCPOS database update in settings
        Setting::set('lastTcposUpdate', AppHelper::getLastTcposUpdate());
        Setting::save();

        return response()->json([
            'message' => 'Tcpos Import launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                'products' => $product_controller_return->original,
                'attributes' => $attribute_controller_return->original,
                'stocks' => $stock_controller_return->original,
                'prices' => $product_controller_prices_return->original,
                'images' => $product_controller_images_return->original,
            ],
        ]);
    }

    /**
     * Import woo all.
     */
    public function importWooAll(): JsonResponse
    {
        $begin = microtime(true);

        activity()->withProperties(['group' => 'import-woo', 'level' => 'start', 'resource' => 'all'])->log('---- Import from Woocommerce ----');

        $sync_product_controller = new SyncProductController;
        $sync_customer_controller = new SyncCustomerController;
        $sync_product_controller_return = $sync_product_controller->importWooProducts();
        $sync_customer_controller_return = $sync_customer_controller->importWooCustomers();

        $end = microtime(true) - $begin;

        return response()->json([
            'message' => 'Woo Import launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                'products' => $sync_product_controller_return->original,
                'customers' => $sync_customer_controller_return->original,
            ],
        ]);
    }

    /**
     * Check last tcpos database update and check if need import.
     */
    public function needImportFromTcpos()
    {
        $response = Http::timeout(1000)->get(env('TCPOS_API_WOND_URL').'/getLastRefreshTimestamp')->json();

        $tcposTimestamp = data_get($response, 'getLastRefreshTimestamp.timestamp');
        $localTimestamp = Setting::get('lastTcposUpdate', null);

        if ($localTimestamp == $tcposTimestamp) {
            return false;
        } else {
            Setting::set('lastTcposUpdate', $tcposTimestamp);
            Setting::save();

            return true;
        }

    }

    /**
     * Check last woo database orders update and check if need import.
     */
    public function needOrdersImportFromWoo()
    {
        $localTimestamp = Setting::get('lastWooUpdate', null);
        if (isset($localTimestamp)) {
            $orders = Order::all(['after' => $localTimestamp]);
            $ordersCount = $orders->count();
            if ($ordersCount > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            $firstOrder = Order::all()->first();
            $firstOrderTimestamp = data_get($firstOrder, 'date_modified');
            Setting::set('lastWooUpdate', $firstOrderTimestamp);
            Setting::save();

            return true;
        }
    }
}
