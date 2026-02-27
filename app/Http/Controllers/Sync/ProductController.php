<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Jobs\SyncProductCreate;
use App\Jobs\SyncProductDelete;
use App\Jobs\SyncProductUpdate;
use App\Models\Product as TcposProduct;
use App\Models\ProductImage as TcposProductImage;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Get all woo products.
     */
    public function getWooProducts()
    {
        $products = Product::all(['per_page' => 100, 'page' => 1]);
        $products2 = Product::all(['per_page' => 100, 'page' => 2]);
        $products3 = Product::all(['per_page' => 100, 'page' => 3]);
        $products4 = Product::all(['per_page' => 100, 'page' => 4]);
        $products5 = Product::all(['per_page' => 100, 'page' => 5]);
        $products6 = Product::all(['per_page' => 100, 'page' => 6]);
        $products7 = Product::all(['per_page' => 100, 'page' => 7]);
        $products8 = Product::all(['per_page' => 100, 'page' => 8]);
        $products9 = Product::all(['per_page' => 100, 'page' => 9]);
        $products10 = Product::all(['per_page' => 100, 'page' => 10]);

        return $products->merge($products2)->merge($products3)->merge($products4)->merge($products5)->merge($products6)->merge($products7)->merge($products8)->merge($products9)->merge($products10);
    }

    /**
     * Get all tcpos products.
     */
    public function getTcposProducts()
    {
        return TcposProduct::all();
    }

    /**
     * Import all woo products.
     */
    public function importWooProducts(): JsonResponse
    {
        $wooResourcesDeleted = Woo::where('resource', 'product')->delete();
        $wooResources = $this->getWooProducts();

        foreach ($wooResources as $item) {
            $product = new Woo;
            $product->resource = 'product';
            $product->_wooId = $item->id;
            $product->_tcposCode = $item->sku;
            $product->data = $item;
            $product->save();
        }

        return response()->json([
            'message' => 'Done',
            'count' => $wooResources->count(),
        ]);
    }

    /**
     * Sync products.
     */
    public function sync()
    {
        $tcposResources = TcposProduct::all();
        $wooResources = Woo::where('resource', 'product')->get();

        if ($tcposResources->count() == 0) {
            activity()->withProperties(['group' => 'sync', 'level' => 'warning', 'resource' => 'products'])->log('No product retrieved from API (got an empty array). Prevent to delete all the production...');

            return response()->json([
                'message' => 'No product retrieved from API (got an empty array). Prevent to delete all the production...',
            ], 400);
        }

        $count_product_create = 0;
        $count_product_not_found = 0;
        $count_product_untouched = 0;
        $count_product_found = 0;
        $count_product_update = 0;
        $count_product_delete = 0;

        // The loop: from tcpos to Woo
        foreach ($tcposResources as $tcposItem) {
            // For testing only a product
            //if ($tcposItem->_tcposCode != 6578) {continue;}

            $match = Woo::where('resource', 'product')->where('_tcposCode', $tcposItem->_tcposCode)->first();

            // If tcpos item not found in Woo, check and create it
            if (empty($match)) {
                $count_product_not_found += 1;
                // Check stock quantity
                if ($tcposItem->isStockRuleCorrect()) {
                    // Create it
                    $data = $this->dataForWoo($tcposItem, $match);
                    //Product::create($data);
                    SyncProductCreate::dispatch($data);
                    $count_product_create += 1;

                    continue;
                }
                $count_product_untouched += 1;

                continue;
            }
            // If tcpos item found in Woo, check and update
            $count_product_found += 1;

            // Check stock quantity
            if ($tcposItem->isStockRuleCorrect()) {
                // Check if local database has update sync_action for the product to avoid update for nothing
                if ($tcposItem->needToUpdate()) {
                    // Update it
                    $data = $this->dataForWoo($tcposItem, $match);
                    //Product::update($match->_wooId, $data);
                    SyncProductUpdate::dispatch($match->_wooId, $data);
                    $count_product_update += 1;

                    continue;
                } else {
                    $count_product_untouched += 1;
                }
            } else {
                // Delete it
                //Product::delete($match->_wooId);
                SyncProductDelete::dispatch($match->_wooId);
                $count_product_delete += 1;

                continue;
            }
        }

        // The reverse loop: from Woo to tcpos
        foreach ($wooResources as $wooItem) {
            if (empty($wooItem->_wooId)) {
                continue;
            }

            $match = TcposProduct::where('_tcposCode', $wooItem->_tcposCode)->first();

            if (empty($match)) {
                // Delete it
                //Product::delete($match->_wooId);
                SyncProductDelete::dispatch($wooItem->_wooId);
                $count_product_delete += 1;

                continue;
            }
        }

        activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'products'])->log('Products sync queued |  '.$count_product_update.' to update, '.$count_product_create.' to create, '.$count_product_delete.' to delete, '.$count_product_untouched.' to untouch');

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_products_in_tcpos' => $tcposResources->count(),
            'count_products_in_woo' => $wooResources->count(),
            'count_product_found' => $count_product_found,
            'count_product_not_found' => $count_product_not_found,
            'count_product_untouched' => $count_product_untouched,
            'count_product_update' => $count_product_update,
            'count_product_create' => $count_product_create,
            'count_product_delete' => $count_product_delete,
        ]);
    }

    /* OBSOLETE TO DELETE */
    /**
     * isStockRuleCorrect.
     */
    public function isStockRuleCorrect($tcposProduct)
    {
        $category = $tcposProduct->category;
        $categoryRule = data_get(config('cdv.categories'), $category);

        // Category not found in config
        if (empty($categoryRule)) {
            return false;
        }
        // Rule set in config do not manage stock
        if (! data_get($categoryRule, 'manage_stock')) {
            return true;
        }
        // Product stock quantity is superior as the minimal set in config
        if ($tcposProduct->stock() >= data_get($categoryRule, 'min_stock_quantity')) {
            return true;
        }
    }

    /**
     * isStockManaged.
     */
    public function isStockManaged($tcposProduct)
    {
        $category = $tcposProduct->category;
        $categoryRule = data_get(config('cdv.categories'), $category);

        // Rule set in config do not manage stock
        if (! data_get($categoryRule, 'manage_stock')) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Prepare data for Woo.
     */
    public function dataForWoo($tcposProduct, $wooProduct = null)
    {
        // Attributes
        $attributes = [];
        foreach (config('cdv.wc_attribute_ids') as $key => $value) {
            $option = data_get($tcposProduct->attributesArray(), $key.'.name', data_get($tcposProduct->attributesArray(), $key)) ?? null;
            if (empty($option)) {
                continue;
            }
            $attributes[] = [
                'id' => $value,
                'position' => 0,
                'visible' => true,
                'variable' => false,
                'options' => [$option],
            ];
        }

        // Data
        $data = [
            'name' => $tcposProduct->name,
            'description' => $tcposProduct->description,
            'sku' => (string) $tcposProduct->_tcposCode,
            'weight' => $tcposProduct->weight,
            'price' => (string) data_get($tcposProduct->pricesRelations, '2.price'),
            'regular_price' => (string) data_get($tcposProduct->pricesRelations, '2.price'),
            'stock_quantity' => (int) $tcposProduct->stock(),
            'manage_stock' => $this->isStockManaged($tcposProduct),
            'attributes' => $attributes,
            'categories' => [['id' => data_get(config('cdv.categories'), $tcposProduct->category.'.category_id')]],
            //'images' => $images,
            'meta_data' => [
                ['key' => config('cdv.wc_meta_tcpos_id'), 'value' => $tcposProduct->_tcposId],
                ['key' => config('cdv.wc_meta_minimum_allowed_quantity'), 'value' => $tcposProduct->minQuantity],
                ['key' => config('cdv.wc_meta_maximum_allowed_quantity'), 'value' => $tcposProduct->maxQuantity],
                ['key' => config('cdv.wc_meta_on_site_price'), 'value' => data_get($tcposProduct->pricesRelations, '1.price')],
            ],
        ];

        // Images
        $productImage = TcposProductImage::where('_tcpos_product_id', $tcposProduct->_tcposId)->first();
        $dist_image_url = $tcposProduct->imageUrl();
        if (isset($wooProduct) && $productImage->hash != null) {
            //There is a tcpos image and a wooProduct
            if (data_get($wooProduct->data, 'images.0.name') != null) {
                //There is an existing image.
                if (data_get($wooProduct->data, 'images.0.name') != $productImage->hash) {
                    //There is an existing image but not match the tcpos hash one : upload
                    $data['images'] = [['name' => $productImage->hash, 'src' => $dist_image_url]];
                    activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'products'])->log('Will update the product (id:'.$tcposProduct->_tcposId.' UGS:'.$tcposProduct->_tcposCode.') image with : '.$dist_image_url);
                } else {
                    //There is an existing image that match the tcpos hash one : keep it
                    //$data['images'] = $wooProduct->data->images;
                    //activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'products'])->log('Will update the product (id:'.$tcposProduct->_tcposId.' UGS:'.$tcposProduct->_tcposCode.') without upddating the image');
                }
            }
            if (data_get($wooProduct->data, 'images.0.name') == null) {
                //There is no existing image : upload
                $data['images'] = [['name' => $productImage->hash, 'src' => $dist_image_url]];
                activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'products'])->log('Will update the product (id:'.$tcposProduct->_tcposId.' UGS:'.$tcposProduct->_tcposCode.') with a new image : '.$dist_image_url);
            }
        } else {
            //There is no tcpos image
            $data['images'] = [];
        }
        //There is no wooProduct: so create a product and if an image exists, add it
        if (empty($wooProduct) && $productImage && $productImage->hash != null) {
            $data['images'] = [['name' => $productImage->hash, 'src' => $dist_image_url]];
            activity()->withProperties(['group' => 'sync', 'level' => 'info', 'resource' => 'products'])->log('Will create the product (id:'.$tcposProduct->_tcposId.' UGS:'.$tcposProduct->_tcposCode.') with a new image : '.$dist_image_url);
        }

        return $data;
    }
}
