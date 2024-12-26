<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ImportProductStock;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Facades\Http;

class StockController extends Controller
{
    /**
     * Show the stock by id.
     */
    public function getStock($id)
    {
        $req = Http::withOptions([
            'verify' => false,
        ])->get(env('TCPOS_API_CDV_URL').'/getarticlesstock/id/'.$id);
        $response = $req->json();
        $data = data_get($response, 'STOCK');

        return $data;
    }

    /**
     * Import products stocks.
     */
    public function importStocks()
    {

        //Stock::truncate();

        $ids = Product::all()->pluck('_tcposId')->all();

        foreach ($ids as $keyId => $valueId) {
            ImportProductStock::dispatch($valueId);
        }

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'job', 'resource' => 'stocks'])->log('Stocks import from TCPOS queued');

        return response()->json([
            'message' => 'job launched. See /jobs',
        ]);
    }
}
