<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
//use App\Jobs\SyncOrderCreate;

class OrderController extends Controller
{
    /**
     * Get all woo customers.
     */
    public function getWooOrders()
    {
        $orders = Order::all(['status' => 'processing', 'per_page' => 100, 'page' => 1]);
        return $orders;
    }


    /**
     * Sync customers.
     */
    public function sync()
    {
        $wooOrders = $this->getWooOrders();

        $count_order_create = 0;
        $count_order_manual = 0;
        $count_order_untouched = 0;
        $orders = [];

        foreach ($wooOrders as $wooOrder) {

            $keyWooOrderIdMetaData = array_search(config('cdv.wc_meta_tcpos_order_id'), array_column($wooOrder->meta_data, 'key'));
            $valueWooOrderIdMetaData = data_get($wooOrder->meta_data, $keyWooOrderIdMetaData.'.value');

            // - Order is in progress
            // - Has not been already synchronized (meta_tcposOrderId to null)
            // - Does not use voucher (bon cadeau) in the process (tcpos does not support voucher usage in order)
            if (empty($valueWooOrderIdMetaData) && $this->getVoucherCode($wooOrder) != 'chatelin') {

                /*$metaDataArray = $wooOrder->meta_data;
                metaDataArray[] = [config('cdv.wc_meta_tcpos_order_id') => null ];
                $data = [
                    'meta_data' => $metaDataArray,
                ];
                Order::update($wooOrder->id, $data);
                */
                $this->createTcposOrder($wooOrder);

                $count_order_create += 1;
            }     
            // - Order is in progress
            // - Has not been already synchronized (meta_tcposOrderId to null)
            // - Does use voucher (bon cadeau) in the process
            elseif (empty($valueWooOrderIdMetaData) && $this->getVoucherCode($wooOrder) == 'chatelin') {
                //dd($wooOrder);
                $count_order_manual += 1;
            } else {
                // Nothing
                $count_order_untouched += 1;
            }
        }

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'woo_orders_processing' => $wooOrders->count(),
            'count_order_create' => $count_order_create,
            'count_order_manual' => $count_order_manual,
            'count_order_untouched' => $count_order_untouched,
        ]);
    }

    /**
     * Get order voucher code.
     */
    public function getVoucherCode($wooOrder)
    {
        foreach ($wooOrder->coupon_lines as $coupon) {
            return data_get($coupon, 'code', false);
        }
    }

    /**
     * Create tcpos order.
     */
    public function createTcposOrder($wooOrder)
    {
        $items = [];
        foreach ($wooOrder->line_items as $item) {
            $items[] = [
                'article' => [
                    'id' => data_get($item, 'tcpos_id'),
                    'quantity' => data_get($item, 'quantity'),
                    'price' => data_get($item, 'price'),
                    'priceLevelId' => config('cdv.default_price_level_id'),
                    ]
                ];
        }
        foreach ($wooOrder->shipping_lines as $item) {
            $items[] = [
                'article' => [
                    'id' => data_get($item, 'tcpos_id'),
                    'price' => data_get($item, 'total'),
                    'priceLevelId' => config('cdv.default_price_level_id'),
                    ]
                ];
        }

        $data = [
            'date' => $wooOrder->date_created,
            'customerId' => config('cdv.default_customer_id'),
            'shopId' => config('cdv.default_shop_id'),
            'orderType' => config('cdv.default_order_type'),
            'priceLevelId' => config('cdv.default_price_level_id'),
            'total' => $wooOrder->total,
            'itemList' => $items,
        ];
        $dataLine = '"data": {
                "date": '.$wooOrder->date_created.',
                "customerId": '.config('cdv.default_customer_id').',
                "shopId": '.config('cdv.default_shop_id').',
                "orderType": '.config('cdv.default_order_type').',
                "priceLevelId": '.config('cdv.default_price_level_id').',
                "total": '.$wooOrder->total.',
                "itemList": '.json_encode($items).',
            }';
        $response = Http::get(env('TCPOS_API_WOND_URL').'/createOrder?data='.$dataLine);
        dd($response->json());
    }
}
