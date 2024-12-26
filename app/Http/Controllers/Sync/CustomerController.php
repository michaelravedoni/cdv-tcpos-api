<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Jobs\SyncCustomerUpdate;
use App\Models\Customer as TcposCustomer;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;

class CustomerController extends Controller
{
    /**
     * Get all woo customers.
     */
    public function getWooCustomers()
    {
        $customers = Customer::all(['per_page' => 100, 'page' => 1]);
        $customers2 = Customer::all(['per_page' => 100, 'page' => 2]);
        $customers3 = Customer::all(['per_page' => 100, 'page' => 3]);
        $customers4 = Customer::all(['per_page' => 100, 'page' => 4]);

        return $customers->merge($customers2)->merge($customers3)->merge($customers4);
    }

    /**
     * Get all tcpos customers.
     */
    public function getTcposCustomers()
    {
        return TcposCustomer::all();
    }

    /**
     * Import all woo customers.
     */
    public function importWooCustomers()
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
    public function sync()
    {
        $wooResources = Woo::where('resource', 'customer')->get();

        $count_customer_update = 0;
        $count_customer_order_active = 0;
        $count_customer_no_card = 0;
        $customers_order_active = [];

        foreach ($wooResources as $wooCustomer) {

            $wooCustomer = $wooCustomer->data;

            if (empty(data_get($wooCustomer, 'card_number'))) {
                // No update because no card.
                $count_customer_no_card += 1;

                continue;
            }

            $lastOrder = Order::where('customer_id', $wooCustomer->id)->get()->sortByDesc('date_created')->first();
            $lastOrderStatus = data_get($lastOrder, 'status');
            if (isset($lastOrderStatus) && in_array($lastOrderStatus, ['pending', 'processing', 'on-hold'])) {
                // No update car une commande ouverte ou en traitement
                $count_customer_order_active += 1;
                $customers_order_active[] = ['customer' => $wooCustomer, 'lastOrder' => $lastOrder];

                continue;
            }

            $customer_controller = new \App\Http\Controllers\Api\V1\CustomerController;
            $tcposCustomerFund = $customer_controller->getCustomerFunds(data_get($wooCustomer, 'card_number'));
            $tcposCustomer = $customer_controller->getCustomer(data_get($wooCustomer, 'card_number'));
            $tcposCustomerAccountType = data_get($tcposCustomer->original, 'accountType');

            //$metaDataArray = $wooCustomer->meta_data;
            //$metaDataArray = array_merge($metaDataArray,[]);
            $metaDataArray = [
                ['key' => config('cdv.wc_meta_customer_account_type'), 'value' => $tcposCustomerAccountType],
                ['id' => 999, 'key' => 'account_funds', 'value' => $tcposCustomerFund],
            ];

            $data = [
                'account_funds' => $tcposCustomerFund,
                'meta_data' => [$metaDataArray],
            ];

            $customers_update[] = ['customer' => $wooCustomer];

            //Customer::update($customer_id, $data);
            SyncCustomerUpdate::dispatch($wooCustomer->id, $data);
            $count_customer_update += 1;
        }

        activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'customers'])->log('Customers sync queued |  '.$count_customer_update.' update, '.$count_customer_order_active.' with an active order and '.$count_customer_no_card.' without card');

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_customer_update' => $count_customer_update,
            'count_customer_order_active' => $count_customer_order_active,
            'count_customer_no_card' => $count_customer_no_card,
            //'customers_order_active' => $customers_order_active,
            //'customers_update' => $customers_update,
        ]);
    }
}
