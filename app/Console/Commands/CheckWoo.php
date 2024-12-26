<?php

namespace App\Console\Commands;

use App\Models\Product as ModelsProduct;
use App\Models\ProductImage;
use App\Models\Woo;
use AppHelper;
use Illuminate\Console\Command;

class CheckWoo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:woo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check everthing is ok with woocommerce';

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
        $this->line('Check if everthing is ok with woocommerce');

        // Detect if there is a problem with images
        // Get tcposProductImages
        $tcposProductImages = ProductImage::select(['id', '_tcpos_product_id', 'hash'])->get();

        // Get wooProducts
        $wooProducts = Woo::all();
        $wooProducts = $wooProducts->map(function ($wooProduct) {
            return [
                'id' => $wooProduct->id,
                '_wooId' => $wooProduct->_wooId,
                '_tcposId' => $wooProduct->_tcposId ?? AppHelper::getMetadataValueFromKey(data_get($wooProduct, 'data.meta_data'), config('cdv.wc_meta_tcpos_id')),
                '_tcposCode' => $wooProduct->_tcposCode,
                'permalink' => data_get($wooProduct, 'data.permalink'),
                'has_image' => data_get($wooProduct, 'data.images.0.src') ? true : false,
                'image_src' => data_get($wooProduct, 'data.images.0.src'),
            ];
        });

        // Find where has no image
        $productIdsToSync = [];
        foreach ($tcposProductImages as $key => $tcposProductImage) {
            $checkedWooProduct = $wooProducts->firstWhere('_tcposId', $tcposProductImage->_tcpos_product_id);
            if (! empty($checkedWooProduct)) {
                if (! data_get($checkedWooProduct, 'has_image') && $tcposProductImage->hash != null) {
                    $productIdsToSync[] = $tcposProductImage->_tcpos_product_id;
                }
                if (! data_get($checkedWooProduct, 'has_image')) {
                    activity()->withProperties(['group' => 'check', 'level' => 'warning', 'resource' => 'products'])->log('No product image in Woocommerce | tcposId:'.$tcposProductImage->_tcpos_product_id.' wooId:'.data_get($checkedWooProduct, '_wooId').' tcposCode:'.data_get($checkedWooProduct, '_tcposCode'));
                }
            }
        }

        // Prepare tu update
        // $productIdsToSync = [1976, 11976]; // testing
        foreach ($productIdsToSync as $productIdToSync) {
            $modelProduct = ModelsProduct::where('_tcposId', $productIdToSync)->first();
            $modelProduct->sync_action = 'update';
            $modelProduct->save();

            activity()->withProperties(['group' => 'check', 'level' => 'warning', 'resource' => 'products'])->log('Product image will be updated in Woocommerce | tcposId:'.$productIdToSync);
        }

        if (count($productIdsToSync) > 0) {
            $this->line('TCPOS Ids that will be updated : '.implode(', ', $productIdsToSync));
        }

        $this->info('Check done.');
    }
}
