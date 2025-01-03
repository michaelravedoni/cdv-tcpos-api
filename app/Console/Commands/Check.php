<?php

namespace App\Console\Commands;

use App\Utilities\AppHelper;
use App\Mail\OrderProblemCheck;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
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
        $orders = Order::all(['per_page' => 5, 'page' => 1]);
        foreach ($orders as $order) {
            $tcposOrderId = AppHelper::getMetadataValueFromKey($order->meta_data, config('cdv.wc_meta_tcpos_order_id'));
            if (empty($tcposOrderId) && in_array($order->status, ['processing', 'on-hold']) && \Carbon\Carbon::parse($order->date_created) < \Carbon\Carbon::now()->addMinutes(60)) {
                // problème de synchronisation détecté
                $this->line('Order sync problem detected.');
                Mail::to('charpin@chateaudevilla.ch')->send(new OrderProblemCheck);
                activity()->withProperties(['group' => 'email', 'level' => 'warning', 'resource' => 'orders'])->log('Problem detected for Order #'.$order->id.'. Email sent.');
            }
        }

        $this->info('Check done.');

        return self::SUCCESS;
    }
}
