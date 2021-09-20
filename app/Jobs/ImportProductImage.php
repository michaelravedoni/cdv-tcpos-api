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

        $product = Product::where('_tcposId', $this->id)->first();

        if (empty($data)) {
            //No image found
            $product->imageHash = null;
            $product->save();
            
            activity()->withProperties(['group' => 'import-tcpos', 'level' => 'warning', 'resource' => 'images'])->log('Product image not found in tcpos database : '.$this->id);
            return;
        }

        if ($product->imageHash == md5($data)) {
            activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'images'])->log('Product image already saved in the database and filesystem : '.$this->id);
            return;
        }

        $image = $data;
        $image = str_replace(' ', '+', $image);
        $imageDecode = base64_decode($image);
        $path = env('TCPOS_PRODUCTS_IMAGES_BASE_PATH').'/'.$this->id.'.jpg';
        Storage::disk('public')->put($path, $imageDecode);

        $product->imageHash = md5($data);
        $product->save();

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'images'])->log('Product image imported in the database and filesystem from tcpos : '.$this->id);
    }
}
