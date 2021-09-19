<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Sync\ProductController;
use App\Http\Controllers\Sync\AttributeController;
use App\Http\Controllers\Sync\CustomerController;
use App\Http\Controllers\Sync\OrderController;

class SyncController extends Controller
{
    /**
     * Sync all.
     */
    public function all()
    {
        $begin = microtime(true);

        activity()->log('Sync: --START-- Sync all');

        $product_controller = new ProductController;
        $attribute_controller = new AttributeController;
        $customercontroller = new CustomerController;

        //$product_controller_return = $product_controller->sync();
        $attribute_controller_return = $attribute_controller->sync();
        $customercontroller_return = $customercontroller->sync();

        $end = microtime(true) - $begin;

        activity()->withProperties(['duration' => $end])->log('Sync: --END-- Sync done | See /jobs for all sync jobs');

        return response()->json([
            'message' => 'Sync launched. Wait and see /jobs',
            'time' => $end,
            'imports' => [
                //'products' => $product_controller_return->original,
                'attributes' => $attribute_controller_return->original,
                'customers' => $customercontroller_return->original,
            ],
        ]);
    }
}
