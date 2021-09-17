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
use Illuminate\Support\Facades\Storage;
use App\Models\Product;

class ImportProductImage implements ShouldQueue
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
        $req = Http::get(env('TCPOS_API_WOND_URL').'/getImage?id='.$this->id);
        $response = $req->json();
        $data = data_get($response, 'getImage.imageList.0.bitmapFile');

        $image = $data;
        $image = str_replace(' ', '+', $image);
        $imageDecode = base64_decode($image);
        $path = env('TCPOS_PRODUCTS_IMAGES_BASE_PATH').'/'.$this->id.'.jpg';
        Storage::disk('public')->put($path, $imageDecode);

        $product = Product::where('_tcposId', $this->id);
        $product->imageHash = md5($data);
        $product->save();

    }
}
