<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
use App\Jobs\SyncOrderUpdate;

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
                // Create order in TCPOS
                $this->createTcposOrder($wooOrder);
                $count_order_create += 1;
            }     
            // - Order is in progress
            // - Has not been already synchronized (meta_tcposOrderId to null)
            // - Does use voucher (bon cadeau) in the process
            elseif (empty($valueWooOrderIdMetaData) && $this->getVoucherCode($wooOrder) == 'chatelin') {
                // Nothing
                $count_order_manual += 1;
            } else {
                // Nothing
                $count_order_untouched += 1;
            }
        }

        activity()->withProperties(['group' => 'sync', 'level' => 'start', 'resource' => 'orders'])->log('Orders sync started | '.$wooOrders->count().' processing orders found, '.$count_order_create.' to create, '.$count_order_manual.' to be processed manually and '.$count_order_untouched.' untouched.');

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
     * Create payment data for TCPOS.
     */
    public function createPaymentTcposData($wooOrder)
    {
        $paymentConfig = data_get(config('cdv.payment_methods'), data_get($wooOrder, 'payment_method'));

        if (isset($paymentConfig)) {
            $paymentData = [
                'payments' => [
                    [
                        'paymentType' => data_get($paymentConfig, 'type'),
                        'paymentNotes' => data_get($paymentConfig, 'note'),
                        'paymentAmount' => data_get($wooOrder, 'total'),
                    ]
                ]
            ];
        } else {
            $paymentData = [
                'payments' => [
                    [
                        'paymentType' => 'cash',
                        'paymentNotes' => 'Non payé. Paiement créé artificiellement pour un test.',
                        'paymentAmount' => data_get($wooOrder, 'total'),
                    ]
                ]
            ];
            //$paymentData = [];
            activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'orders'])->log('The order payment #'.$wooOrder->id.' has no payment. A fake one has been created for testing purpose.');
        }
        return $paymentData;
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

        $stringShippingAddress = $wooOrder->shipping->first_name.' '.$wooOrder->shipping->last_name.' | '.$wooOrder->shipping->address_1.', '.$wooOrder->shipping->postcode.' '.$wooOrder->shipping->city.' '.$wooOrder->shipping->country;

        $data = [
            'data' => [
                'date' => now()->addDay()->toDateTimeLocalString(),
                'customerId' => config('cdv.default_customer_id'),
                'shopId' => config('cdv.default_shop_id'),
                'orderType' => config('cdv.default_order_type'),
                'priceLevelId' => config('cdv.default_price_level_id'),
                'total' => $wooOrder->total,
                'transactionComment' => 'Commande Woocommerce #'.$wooOrder->id.' à livrer chez '.$stringShippingAddress,
                'itemList' => $items,
            ]
        ];
        
        // Générer un token pour TCPOS
        $requestToken = Http::get(env('TCPOS_API_WOND_URL').'/login?user='.env('TCPOS_API_WOND_USER').'&password='.env('TCPOS_API_WOND_PASSWORD'));
        $token = data_get($requestToken->json(), 'login.customerProperties.token', false);

        // S'il y a a un token: créer la commande dans TCPOS
        if ($token) {
            $requestOrder = Http::get(env('TCPOS_API_WOND_URL').'/createOrder?token='.urlencode($token).'&data='.urlencode(json_encode($data)));
            $dataOrder = $requestOrder->json();
            $dataOrderResponse = data_get($dataOrder, 'createOrder.result');

            // S'il y a une erreur dans la création de la commande
            if ($dataOrderResponse != 'OK') {
                activity()->withProperties(['group' => 'sync', 'level' => 'error', 'resource' => 'orders'])->log('The order #'.$wooOrder->id.' could not be transmitted correctly to TCPOS WOND. Message: '.data_get($dataOrderConfirm, 'createOrder.message'));
                return 'Error: The order could not be transmitted correctly to TCPOS WOND';
            }

            // S'il n'y a pas d'erreur dans la création de la commande: confirmer la commande dans TCPOS
            $requestOrderConfirm = Http::get(env('TCPOS_API_WOND_URL').'/confirmOrder', [
                'token' => $token,
                'shopId' => config('cdv.default_shop_id'),
                'guid' => data_get($dataOrder, 'createOrder.data.guid'), // Get guid from the previous request
                'operation' => config('cdv.default_confirm_order_operation'),
                'payments' => json_encode($this->createPaymentTcposData($wooOrder)),
            ]);
            $dataOrderConfirm = $requestOrderConfirm->json();
            $dataOrderConfirmResponse = data_get($dataOrderConfirm, 'confirmOrder.result');

            // S'il y a une erreur dans la confirmation de la commande
            if ($dataOrderConfirmResponse != 'OK') {
                activity()->withProperties(['group' => 'sync', 'level' => 'error', 'resource' => 'orders'])->log('Error: The order #'.$wooOrder->id.' could not be confirmed correctly to TCPOS WOND. Message: '.data_get($dataOrderConfirm, 'confirmOrder.message'));
                return 'Error: The order could not be confirmed correctly to TCPOS WOND';
            }
            activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'orders'])->log('Order #'.$wooOrder->id.' created in TCPOS WOND');

            // S'il y a une erreur dans la confirmation de la commande : mettons un id sur la commande de Woocommerce
            $wooUpdateOrderData = [
                'meta_data' => [
                    ['key' => config('cdv.wc_meta_tcpos_order_id'), 'value' => data_get($dataOrder, 'createOrder.data.guid')]
                ]
            ];
            // Order::update($this->id, $this->data);
            SyncOrderUpdate::dispatch($wooOrder->id, $wooUpdateOrderData);
            activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'orders'])->log('Order #'.$wooOrder->id.' dispatched to queue');
        } else {
            activity()->withProperties(['group' => 'sync', 'level' => 'error', 'resource' => 'orders'])->log('Token could not be created.');
        }
    }
}
