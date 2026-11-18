<?php

namespace App\Console\Commands;

use App\Utilities\AppHelper;
use Illuminate\Support\Carbon;
use App\Mail\OrderProblemCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Codexshaper\WooCommerce\Facades\Order;
use Spatie\Activitylog\Models\Activity;

class Check extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check everthing is ok';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->line('Check if everthing is ok');

        // Detect if there is a problem with orders (older than 60 minutes without tcpos_order_id)
        $problematicOrders = [];
        $problematicOrderDetails = [];
        $orders = Order::all(['per_page' => 5, 'page' => 1]);
        foreach ($orders as $order) {
            $tcposOrderId = AppHelper::getMetadataValueFromKey($order->meta_data, config('cdv.wc_meta_tcpos_order_id'));
            if (empty($tcposOrderId) && in_array($order->status, ['processing', 'on-hold']) && Carbon::parse($order->date_created)->lt(Carbon::now()->subMinutes(60))) {
                $problematicOrders[] = $order->id;
                $lastError = Activity::query()
                    ->where('properties->resource', 'orders')
                    ->whereIn('properties->level', ['error', 'warning'])
                    ->where('description', 'like', '%#'.$order->id.'%')
                    ->latest('created_at')
                    ->value('description');

                $problematicOrderDetails[] = [
                    'id' => $order->id,
                    'customer' => trim(data_get($order, 'billing.first_name').' '.data_get($order, 'billing.last_name')),
                    'email' => data_get($order, 'billing.email'),
                    'total' => number_format((float) data_get($order, 'total', 0), 2).' '.data_get($order, 'currency', 'CHF'),
                    'status' => $order->status,
                    'date' => Carbon::parse($order->date_created)->timezone('Europe/Zurich')->format('d.m.Y H:i'),
                    'error' => $lastError,
                ];
            }
        }

        if (! empty($problematicOrders)) {
            $this->line('Order sync problem detected for orders: ' . implode(', ', $problematicOrders));

            $lastSent = Cache::get('order_problem_check_sent_at');

            if (! $lastSent || Carbon::parse($lastSent)->lt(now()->subHours(4))) {
                Mail::to('charpin@chateaudevilla.ch')->send(new OrderProblemCheck($problematicOrderDetails));
                Cache::put('order_problem_check_sent_at', now(), now()->addHours(4));
                activity()->withProperties(['group' => 'email', 'level' => 'warning', 'resource' => 'orders', 'orders' => $problematicOrders])->log('Problem detected for Orders. Email sent.');
                $this->line('Order problem email sent.');
            } else {
                $this->line('An email has been sent less than 4 hours ago. Waiting before sending a new one.');
            }
        }

        $this->info('Check done.');

        return self::SUCCESS;
    }
}
