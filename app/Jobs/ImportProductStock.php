<?php

namespace App\Jobs;

use App\Models\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class ImportProductStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $id;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $req = Http::withOptions([
            'verify' => false,
        ])->get(config('cdv.tcpos.api_cdv_url').'/getarticlesstock/id/'.$this->id);
        $response = $req->json();
        $data = data_get($response, 'STOCK');

        // Get product stock in local database
        $localStock = Stock::where('_tcpos_product_id', $this->id)->first();

        $stockData = $data;

        // If a stock in database exists
        if (isset($localStock)) {

            // If the stock is the same as one in the database
            if ($localStock->value == $stockData) {
                $localStock->sync_action = 'none';
                $localStock->save();

                activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'stocks'])->log('Product stock untouched in the local database | tcposId:'.$this->id);

            } else {
                $localStock->value = $stockData;
                $localStock->sync_action = 'update';
                $localStock->save();

                activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'stocks'])->log('Product stock updated in the local database | tcposId:'.$this->id);
            }

        } else {
            // If the stock not exists in database: create it
            $stock = new Stock;
            $stock->value = $stockData;
            $stock->_tcpos_product_id = $this->id;
            $stock->sync_action = 'update';
            $stock->save();

            activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'stocks'])->log('Product stock imported in the local database | tcposId:'.$this->id);
        }
    }
}
