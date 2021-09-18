<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Codexshaper\WooCommerce\Facades\Customer;
use Codexshaper\WooCommerce\Facades\Order;
use App\Models\Customer as TcposCustomer;
use App\Jobs\SyncCustomerUpdate;

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
        return $customers->merge($customers2)->merge($customers3);
    }

    /**
     * Get all tcpos customers.
     */
    public function getTcposCustomers()
    {
        return TcposCustomer::all();
    }



    /**
     * Sync customers.
     */
    public function sync()
    {
        $count_customer_update = 0;
        $count_customer_order_active = 0;
        $count_customer_no_card = 0;
        $customers_order_active = [];
        foreach ($this->getWooCustomers() as $wooCustomer) {
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
            
            //Customer::update($customer_id, $data);
            SyncCustomerUpdate::dispatch($wooCustomer);
            $count_customer_update += 1;
        }

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_customer_update' => $count_customer_update,
            'count_customer_order_active' => $count_customer_order_active,
            'count_customer_no_card' => $count_customer_no_card,
            'customers_order_active' => $customers_order_active,
        ]);
    }
}
