<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use AppHelper;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Support\Facades\Mail;

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
     *
     * @return int
     */
    public function handle()
    {
        $this->line('Check if everthing is ok');

        // Detect if there is a problem with orders
        $orders = Order::all(['per_page' => 5, 'page' => 1]);
        foreach ($orders as $order) {
            $tcposOrderId = AppHelper::getMetadataValueFromKey($order->meta_data, config('cdv.wc_meta_tcpos_order_id'));
            if (empty($tcposOrderId) && $order->status == 'pending' && \Carbon\Carbon::parse($order->date_created) < \Carbon\Carbon::now()->addMinutes(60)) {
                // problème de synchronisation détecté
                $this->line('Order sync problem detected.');
                Mail::plain('Bonjour, un problème de synchronisation a été détecté pour les commandes entre Woocommerce et TCPOS sur {{ $url }}. Veuillez contrôler.',
                ['url' => env('APP_URL')], function ($message) {
                    $message
                    ->to('charpin@chateaudevilla.ch')
                    ->subject('Commandes entre Woocommerce et TCPOS : Problème de synchronisation détecté');
                });
            }
        }

        $this->info('Check done.');
    }
}
