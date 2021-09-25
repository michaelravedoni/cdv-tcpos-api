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
                "shopId": 1,
                "priceLevelId": 14,
                "itemList": [{
                    "article": {
                        "priceLevelId": 2,
                        "id": '.$this->id.',
                        "quantity": 1
                    }
                },
                {
                    "article": {
                        "priceLevelId": 6,
                        "id": '.$this->id.',
                        "quantity": 1
                    }
                },
                {
                    "article": {
                        "priceLevelId": 14,
                        "id": '.$this->id.',
                        "quantity": 1
                    }
                }]
            }
        }');
        $response = $req->json();
        //$data = data_get($response, 'getPrice.data.itemList.0.article');
        $data = data_get($response, 'getPrice.data.itemList');

        foreach ($data as $value) {

            $localPrice = Price::where('_tcpos_product_id', data_get($value, 'article.id'))->where('pricelevelid', data_get($value, 'article.pricelevelid'))->first();

            // If a price in database exists
            if (isset($localPrice)) {

                $tcposPrice = (object) data_get($value, 'article');

                // If the hash is the same as one in the database
                if ($localPrice->price == $tcposPrice->price) {
                    $localPrice->sync_action = 'none';
                    $localPrice->save();

                    activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'prices'])->log('Product price untouched in the local database | tcposId:'.$tcposPrice->id);

                } else {
                    $localPrice->price = $tcposPrice->price;
                    $localPrice->discountedprice = $tcposPrice->discountedprice;
                    $localPrice->sync_action = 'update';
                    $localPrice->save();

                    activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'prices'])->log('Product price updated in the local database | tcposId:'.$tcposPrice->id);
                }

            } else {
                // If the price not exists in database: create it
                $tcposPrice = (object) data_get($value, 'article');
                $price = new Price;
                $price->_tcpos_product_id = $tcposPrice->id;
                $price->price = $tcposPrice->price;
                $price->discountedprice = $tcposPrice->discountedprice;
                $price->pricelevelid = $tcposPrice->pricelevelid;
                $price->sync_action = 'update';
                $price->save();

                activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'prices'])->log('Product price imported in the local database | tcposId:'.$tcposPrice->id);
            }
        }
    }
}
