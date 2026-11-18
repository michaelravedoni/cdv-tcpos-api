<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Jobs\SyncCustomerUpdate;
use App\Models\Customer as TcposCustomer;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    /**
     * Get all woo customers (paginated dynamically, filtered to card holders when supported).
     */
    public function getWooCustomers()
    {
        $allCustomers = collect();
        $perPage = 100;
        $page = 1;

        do {
            $batch = Customer::all([
                'per_page' => $perPage,
                'page' => $page,
                'has_card' => 1,
            ]);

            if ($batch->isEmpty()) {
                break;
            }

            $allCustomers = $allCustomers->merge($batch);
            $page++;
        } while ($batch->count() === $perPage);

        return $allCustomers;
    }

    /**
     * Get all tcpos customers. Not in production.
     */
    public function getTcposCustomers()
    {
        // return TcposCustomer::all();
    }

    /**
     * Import all woo customers.
     */
    public function importWooCustomers(): JsonResponse
    {
        $wooResourcesDeleted = Woo::where('resource', 'customer')->delete();
        $wooResources = $this->getWooCustomers();

        foreach ($wooResources as $item) {
            $product = new Woo;
            $product->resource = 'customer';
            $product->_wooId = $item->id;
            $product->data = $item;
            $product->save();
        }

        return response()->json([
            'message' => 'Done',
            'count' => $wooResources->count(),
        ]);
    }

    /**
     * Sync customers.
     */
    public function sync(): JsonResponse
    {
        $wooResources = Woo::where('resource', 'customer')->get();

        $count_customer_update = 0;
        $count_customer_unchanged = 0;
        $count_customer_order_active = 0;
        $count_customer_no_card = 0;
        $count_customer_error = 0;
        $customer_controller = new \App\Http\Controllers\Api\V1\CustomerController;

        foreach ($wooResources as $wooCustomerRecord) {
            $wooCustomer = $wooCustomerRecord->data;
            $cardNumber = data_get($wooCustomer, 'card_number');

            if (empty($cardNumber)) {
                $count_customer_no_card += 1;

                continue;
            }

            $lastOrder = Order::where('customer_id', $wooCustomer->id)->get()->sortByDesc('date_created')->first();
            $lastOrderStatus = data_get($lastOrder, 'status');
            if (isset($lastOrderStatus) && in_array($lastOrderStatus, ['pending', 'processing', 'on-hold'])) {
                $count_customer_order_active += 1;

                continue;
            }

            $tcposResponse = $customer_controller->getCustomer($cardNumber);
            if ($tcposResponse->getStatusCode() !== 200) {
                $count_customer_error += 1;
                activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'customers'])->log('TCPOS customer lookup failed | WooId:'.$wooCustomer->id.' Card:'.$cardNumber.' Status:'.$tcposResponse->getStatusCode());

                continue;
            }

            $tcposData = $tcposResponse->getData(true);
            $tcposCustomerFund = data_get($tcposData, 'accountFunds');
            $tcposCustomerAccountType = data_get($tcposData, 'accountType');

            if (! is_numeric($tcposCustomerFund)) {
                $count_customer_error += 1;
                activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'customers'])->log('TCPOS customer funds invalid | WooId:'.$wooCustomer->id.' Card:'.$cardNumber);

                continue;
            }

            $targetFunds = (float) $tcposCustomerFund;
            $currentFunds = (float) data_get($wooCustomer, 'account_funds', 0);
            $accountTypeKey = config('cdv.wc_meta_customer_account_type');
            $currentAccountType = data_get(
                collect(data_get($wooCustomer, 'meta_data', []))->firstWhere('key', $accountTypeKey),
                'value'
            );

            if (abs($targetFunds - $currentFunds) <= 0.001 && $currentAccountType === $tcposCustomerAccountType) {
                $count_customer_unchanged += 1;

                continue;
            }

            $data = [
                'account_funds' => $targetFunds,
                'meta_data' => [
                    ['key' => $accountTypeKey, 'value' => $tcposCustomerAccountType],
                ],
            ];

            SyncCustomerUpdate::dispatch($wooCustomer->id, $data);
            $count_customer_update += 1;
        }

        activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'customers'])->log('Customers sync queued | '.$count_customer_update.' update, '.$count_customer_unchanged.' unchanged, '.$count_customer_order_active.' active order, '.$count_customer_no_card.' without card, '.$count_customer_error.' errors');

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_customer_update' => $count_customer_update,
            'count_customer_unchanged' => $count_customer_unchanged,
            'count_customer_order_active' => $count_customer_order_active,
            'count_customer_no_card' => $count_customer_no_card,
            'count_customer_error' => $count_customer_error,
        ]);
    }
}
