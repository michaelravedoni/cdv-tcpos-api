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
use App\Models\Price;

class ImportProductPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    use IsMonitored;

    public $id;

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
        $req = Http::get(env('TCPOS_API_WOND_URL').'/getPrice?data={
            "data": {
            "customerId": 897,
            "shopId": 1,
            "date": "2025-11-02T15:23:56",
                "priceLevelId": 14,
                "itemList": [{
                    "article": {
                        "id": '.$this->id.',
                        "quantity": 1
                    }
                }]
            }
        }');
        $response = $req->json();
        $data = data_get($response, 'getPrice.data.itemList.0.article');

        $priceData = (object) $data;
            
        $price = new Price;
        $price->_tcpos_product_id = $priceData->id;
        $price->price = $priceData->price;
        $price->discountedprice = $priceData->discountedprice;
        $price->pricelevelid = $priceData->pricelevelid;
        $price->save();
    }
}
