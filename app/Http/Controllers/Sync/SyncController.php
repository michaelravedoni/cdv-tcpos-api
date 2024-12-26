<?php

namespace App\Http\Controllers\Sync;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

class SyncController extends Controller
{
    /**
     * Sync all.
     */
    public function all(): JsonResponse
    {
        $begin = microtime(true);

        activity()->withProperties(['group' => 'sync', 'level' => 'start', 'resource' => 'all'])->log('---- Sync all between TCPOS & Woocommerce ----');

        $product_controller = new ProductController;
        $attribute_controller = new AttributeController;
        $customercontroller = new CustomerController;

        $product_controller_return = $product_controller->sync();
        $attribute_controller_return = $attribute_controller->sync();
        $customercontroller_return = $customercontroller->sync();

        $end = microtime(true) - $begin;

        return response()->json([
            'message' => 'Sync launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                'products' => $product_controller_return->original,
                'attributes' => $attribute_controller_return->original,
                'customers' => $customercontroller_return->original,
            ],
        ]);
    }
}
