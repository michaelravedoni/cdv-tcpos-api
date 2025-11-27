<?php

namespace App\Console\Commands;

use App\Utilities\AppHelper;
use Illuminate\Support\Carbon;
use App\Mail\OrderProblemCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Codexshaper\WooCommerce\Facades\Order;

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

        // Detect if there is a problem with orders
        $problematicOrders = [];
        $orders = Order::all(['per_page' => 5, 'page' => 1]);
        foreach ($orders as $order) {
            $tcposOrderId = AppHelper::getMetadataValueFromKey($order->meta_data, config('cdv.wc_meta_tcpos_order_id'));
            if (empty($tcposOrderId) && in_array($order->status, ['processing', 'on-hold']) && Carbon::parse($order->date_created) < Carbon::now()->addMinutes(60)) {
                $problematicOrders[] = $order->id;
            }
        }

        if (! empty($problematicOrders)) {
            $this->line('Order sync problem detected for orders: ' . implode(', ', $problematicOrders));

            $lastSent = Cache::get('order_problem_check_sent_at');

            if (! $lastSent || Carbon::parse($lastSent)->lt(now()->subHours(4))) {
                Mail::to('charpin@chateaudevilla.ch')->send(new OrderProblemCheck());
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
