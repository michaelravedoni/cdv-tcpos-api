<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use romanzipp\QueueMonitor\Traits\IsMonitored;
use Illuminate\Support\Facades\Http;
use App\Models\Stock;

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
     *
     * @return void
     */
    public function handle()
    {
        $req = Http::withOptions([
            'verify' => false,
        ])->get(env('TCPOS_API_CDV_URL').'/getarticlesstock/id/'.$this->id);
        $response = $req->json();
        $data = data_get($response, 'STOCK');

        $stockData = $data;
        $stock = new Stock;
        $stock->value = $stockData;
        $stock->_tcpos_product_id = $this->id;
        $stock->save();

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'stocks'])->log('Product stock imported in the local database | tcposId:'.$this->id);
    }
}
