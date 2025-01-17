<?php

namespace App\Http\Controllers\Sync;

use App\Utilities\AppHelper;
use Illuminate\Http\Request;
use App\Jobs\SyncOrderUpdate;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Codexshaper\WooCommerce\Facades\Order;
use Codexshaper\WooCommerce\Facades\Customer;

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
    public function sync(): JsonResponse
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
            if (empty($valueWooOrderIdMetaData) && ! $this->doesOrderUseVoucher($wooOrder)) {
                // Create order in TCPOS
                $this->createTcposOrder($wooOrder, 'default');
                $count_order_create += 1;
            }
            // - Order is in progress
            // - Has not been already synchronized (meta_tcposOrderId to null)
            // - Does use voucher (bon cadeau) in the process
            elseif (empty($valueWooOrderIdMetaData) && $this->doesOrderUseVoucher($wooOrder)) {
                // Create order in TCPOS with voucher type
                $this->createTcposOrder($wooOrder, 'voucher');
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
     * Does the order use voucher (bon cadeau) in the process (tcpos does not support voucher usage in order)
     */
    public function doesOrderUseVoucher($wooOrder)
    {
        $codesArray = [];
        foreach ($wooOrder->coupon_lines as $coupon) {
            $codesArray[] = data_get($coupon, 'code');
        }

        foreach ($codesArray as $code) {
            // Si un nombre à 16 chiffres: true
            if (preg_match('/^\d{16}$/', $code)) {
                return true;
            } else {
                continue;
            }
        }

        return false;
    }

    /**
     * Does the customer is member (chatelin)
     */
    public function doesOrderCustomerIsMember($wooOrder)
    {
        $codesArray = [];
        foreach ($wooOrder->coupon_lines as $coupon) {
            $codesArray[] = data_get($coupon, 'code');
        }

        foreach ($codesArray as $code) {
            // Si égale à chatelin: true
            if ($code == 'chatelin') {
                return true;
            } else {
                continue;
            }
        }

        return false;
    }

    /**
     * Get customer TCPSO ID
     */
    public function getTcposCustomerId($wooOrder)
    {
        $WooCustomerId = data_get($wooOrder, 'customer_id');
        // Si le l'id n'est pas trouvé ou la commande est effectuée en mode invité
        if ($WooCustomerId == 0 || empty($WooCustomerId)) {
            activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'orders'])->log('In order #'.$wooOrder->id.' the customer was not found in WooCommerce. The default has set.');

            return config('cdv.default_customer_id');
        }
        $wooCustomer = Customer::find($WooCustomerId);
        $customerCardNum = data_get($wooCustomer, 'card_number');

        if ($customerCardNum != '' || empty($customerCardNum)) {
            $req = Http::withOptions([
                'verify' => false,
            ])->get(env('TCPOS_API_CDV_URL').'/getCustomerDetails/card/'.$customerCardNum);
            $response = $req->json();
            $tcposCustomerId = data_get($response, 'USER.ID');
            if (empty($tcposCustomerId)) {
                activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'orders'])->log('In order #'.$wooOrder->id.' the customer was not found in TCPOS database. The default has set.');

                return config('cdv.default_customer_id');
            }

            return $tcposCustomerId;
        } else {
            activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'orders'])->log('In order #'.$wooOrder->id.' the customer was not found with cardnumber in WooCommer imported customers. The default has set.');

            return config('cdv.default_customer_id');
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
                    ],
                ],
            ];
        } else {
            $paymentData = [
                'payments' => [
                    [
                        'paymentType' => 'cash',
                        'paymentNotes' => 'Non payé. Paiement créé artificiellement pour un test.',
                        'paymentAmount' => data_get($wooOrder, 'total'),
                    ],
                ],
            ];
            //$paymentData = [];
            activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'orders'])->log('The order payment #'.$wooOrder->id.' has no payment. A fake one has been created for testing purpose.');
        }
        // If funds used (customercard / solde de la carte client), add it as payment
        if (! empty(AppHelper::getMetadataValueFromKey($wooOrder->meta_data, '_funds_used'))) {
            $paymentData['payments'][] =
                [
                    'paymentType' => 'customercard',
                    'paymentNotes' => 'Solde client.',
                    'paymentAmount' => AppHelper::getMetadataValueFromKey($wooOrder->meta_data, '_funds_used'),
                ];

            activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'orders'])->log('The order #'.$wooOrder->id.' uses customer funds (solde du compte client) for a total of '.AppHelper::getMetadataValueFromKey($wooOrder->meta_data, '_funds_used').'. The order has to be controlled.');
        }

        return $paymentData;
    }

    /**
     * Create tcpos order.
     */
    public function createTcposOrder($wooOrder, $type = 'default')
    {
        // Assigner l'opéraiton par défaut: Si type est defaut mettre opération TCPOS par défaut sinon 'S' (save)
        $operation = $type == 'default' ? config('cdv.default_confirm_order_operation') : 'S';

        // Créer les produits
        $items = [];
        foreach ($wooOrder->line_items as $item) {
            $items[] = [
                'article' => [
                    'id' => data_get($item, 'tcpos_id'),
                    'quantity' => data_get($item, 'quantity'),
                    'price' => data_get($item, 'price'),
                    'priceLevelId' => config('cdv.default_price_level_id'),
                ],
            ];
        }

        // Ajouter le produit frais de port s'il existe et est plus grand que 0
        if (isset($wooOrder->shipping_total) && $wooOrder->shipping_total > 0) {
            $items[] = [
                'article' => [
                    'id' => config('cdv.tcpos_default_shipping_item_id'),
                    'quantity' => 1,
                    'price' => $wooOrder->shipping_total,
                    'priceLevelId' => config('cdv.default_price_level_id'),
                ],
            ];
        }

        // Ajouter les discounts s'il y a une carte cadeau (pour un total juste sur TCPOS)
        if ($type == 'voucher') {
            $items[] = [
                'article' => [
                    'id' => config('cdv.tcpos_default_discounts_item_id'),
                    'quantity' => 1,
                    'price' => $wooOrder->discount_total,
                    'priceLevelId' => config('cdv.default_price_level_id'),
                ],
            ];
        }

        // Créer la ligne d'adresse pour le commentaire de commande TCPOS
        $stringShippingAddress = $wooOrder->shipping->first_name.' '.$wooOrder->shipping->last_name.' | '.$wooOrder->shipping->address_1.', '.$wooOrder->shipping->postcode.' '.$wooOrder->shipping->city.' '.$wooOrder->shipping->country;
        // Créer la ligne de commentaire liée au bon cadeau, s'il y a un bon cadeau
        $couponCodes = implode(' - ', array_column($wooOrder->coupon_lines, 'code'));
        $stringVoucherComment = $type == 'voucher' ? '. Utilisation du bon cadeau #'.$couponCodes.' pour un rabais total (tous rabais confondus) de '.$wooOrder->discount_total.'.' : null;

        $requestOrderData = [
            'data' => [
                'date' => now()->addHour(2)->toDateTimeLocalString(),
                'customerId' => $this->getTcposCustomerId($wooOrder),
                'shopId' => config('cdv.default_shop_id'),
                'orderType' => config('cdv.default_order_type'),
                'priceLevelId' => config('cdv.default_price_level_id'),
                'transactionComment' => 'Commande Woocommerce #'.$wooOrder->id.' à livrer chez '.$stringShippingAddress.$stringVoucherComment,
                'itemList' => $items,
            ],
        ];

        // Générer un token pour TCPOS
        $requestToken = Http::get(env('TCPOS_API_WOND_URL').'/login?user='.env('TCPOS_API_WOND_USER').'&password='.env('TCPOS_API_WOND_PASSWORD'));
        $token = data_get($requestToken->json(), 'login.customerProperties.token', false);

        // S'il y a a un token: créer la commande dans TCPOS
        if ($token) {
            $requestOrder = Http::get(env('TCPOS_API_WOND_URL').'/createOrder?token='.urlencode($token).'&data='.urlencode(json_encode($requestOrderData)));
            $dataOrder = $requestOrder->json();
            $dataOrderResponse = data_get($dataOrder, 'createOrder.result');

            // S'il y a une erreur dans la création de la commande
            if ($dataOrderResponse != 'OK') {
                activity()->withProperties(['group' => 'sync', 'level' => 'error', 'resource' => 'orders'])->log('The order #'.$wooOrder->id.' could not be transmitted correctly to TCPOS WOND. Message: '.data_get($dataOrder, 'createOrder.message'));

                return 'Error: The order could not be transmitted correctly to TCPOS WOND';
            } else {
                activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'orders'])->log('The order #'.$wooOrder->id.' has been correctly  transmitted to TCPOS WOND. GUID: '.data_get($dataOrder, 'createOrder.data.guid'));
            }

            // S'il n'y a pas d'erreur dans la création de la commande: confirmer la commande dans TCPOS
            $requestOrderConfirmData = [
                'token' => $token,
                'shopId' => config('cdv.default_shop_id'),
                'guid' => data_get($dataOrder, 'createOrder.data.guid'), // Get guid from the previous request
                'operation' => $operation,
            ];

            if ($type == 'default') {
                $requestOrderConfirmData['payments'] = json_encode($this->createPaymentTcposData($wooOrder));
            }

            $requestOrderConfirm = Http::get(env('TCPOS_API_WOND_URL').'/confirmOrder', $requestOrderConfirmData);
            $dataOrderConfirm = $requestOrderConfirm->json();
            $dataOrderConfirmResponse = data_get($dataOrderConfirm, 'confirmOrder.result');

            // S'il y a une erreur dans la confirmation de la commande
            if ($dataOrderConfirmResponse != 'OK') {
                activity()->withProperties(['group' => 'sync', 'level' => 'error', 'resource' => 'orders'])->log('Error: The order #'.$wooOrder->id.' could not be confirmed correctly to TCPOS WOND. Message: '.data_get($dataOrderConfirm, 'confirmOrder.message'));

                return 'Error: The order could not be confirmed correctly to TCPOS WOND';
            }
            activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'orders'])->log('Order #'.$wooOrder->id.' created in TCPOS WOND. GUID: '.data_get($dataOrder, 'createOrder.data.guid'));

            // S'il n'y a pas d'une erreur dans la confirmation de la commande : mettons un id sur la commande de Woocommerce
            $wooUpdateOrderData = [
                'meta_data' => [
                    ['key' => config('cdv.wc_meta_tcpos_order_id'), 'value' => data_get($dataOrder, 'createOrder.data.guid')],
                ],
            ];
            // Order::update($this->id, $this->data);
            SyncOrderUpdate::dispatch($wooOrder->id, $wooUpdateOrderData);
            activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'orders'])->log('Order #'.$wooOrder->id.' dispatched to queue');
        } else {
            activity()->withProperties(['group' => 'sync', 'level' => 'error', 'resource' => 'orders'])->log('Token could not be created.');
        }
    }
}
