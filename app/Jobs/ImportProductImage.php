<?php

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use romanzipp\QueueMonitor\Traits\IsMonitored;

class ImportProductImage implements ShouldQueue
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
        $req = Http::get(env('TCPOS_API_WOND_URL').'/getImage?id='.$this->id);
        $response = $req->json();
        $data = data_get($response, 'getImage.imageList.0.bitmapFile');

        // Get product image in local database
        $productImage = ProductImage::where('_tcpos_product_id', $this->id)->first();

        // If not exists in local database: create one
        if (empty($productImage)) {
            $productImage = new ProductImage;
            $productImage->_tcpos_product_id = $this->id;
        }

        // If no image exists in tcpos: reset hash and label no sync action
        if (empty($data)) {
            //No image found
            $productImage->hash = null;
            $productImage->sync_action = 'none';
            $productImage->save();

            activity()->withProperties(['group' => 'import-tcpos', 'level' => 'warning', 'resource' => 'images'])->log('Product image not found in TCPOS database | tcposId:'.$this->id);

            return;
        }

        // If the image hash is the same in tcpos ans local database: do nothing and label no sync action
        if ($productImage->hash == md5($data)) {
            activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'images'])->log('Product image already saved in the local database and local filesystem | tcposId:'.$this->id);
            $productImage->sync_action = 'none';
            $productImage->save();

            return;
        }

        // If the image hash is not the same in tcpos ans local database: import image, set hash and label sync action as update
        $image = $data;
        $image = str_replace(' ', '+', $image);
        $imageDecode = base64_decode($image);
        $path = env('TCPOS_PRODUCTS_IMAGES_BASE_PATH').'/'.$this->id.'.jpg';
        Storage::disk('public')->put($path, $imageDecode);

        $productImage->hash = md5($data);
        $productImage->sync_action = 'update';
        $productImage->save();

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'images'])->log('Product image imported in the database and filesystem from tcpos | tcposId:'.$this->id);
    }
}
