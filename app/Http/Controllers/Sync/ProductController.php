<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\ProductResource;
use App\Models\Woo;
use Codexshaper\WooCommerce\Facades\Product;
use App\Models\Product as TcposProduct;
use App\Jobs\SyncProductUpdate;
use App\Jobs\SyncProductCreate;
use App\Jobs\SyncProductDelete;

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
        return $products->merge($products2)->merge($products3)->merge($products4)->merge($products5)->merge($products6)->merge($products7)->merge($products8)->merge($products9);
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
    public function importWooProducts()
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
     * Sync customers.
     */
    public function sync()
    {
        $tcposResources = TcposProduct::all();
        $wooResources = Woo::where('resource', 'product')->get();

        $count_product_create = 0;
        $count_product_not_found = 0;
        $count_product_untouched = 0;
        $count_product_found = 0;
        $count_product_update = 0;
        $count_product_delete = 0;

        foreach ($tcposResources as $tcposItem) {
            // For testing only a product
            if ($tcposItem->_tcposId != 13482) {continue;}

            $match = Woo::where('resource', 'product')->where('_tcposCode', $tcposItem->_tcposCode)->first();

            // If tcpos item not found in Woo, check and create it
            if (empty($match)) {
                $count_product_not_found += 1;
                // Check stock quantity
                if ($this->isStockRuleCorrect($tcposItem)) {
                    // Create it
                    $data = $this->dataForWoo($tcposItem, $match);
                    //Product::create($data);
                    //SyncProductCreate::dispatch($data);
                    $count_product_create += 1;
                    continue;
                }
                $count_product_untouched += 1;
                continue;
            }
            // If tcpos item found in Woo, check and update
            $count_product_found += 1;

            // Check stock quantity
            if ($this->isStockRuleCorrect($tcposItem)) {
                // Update it
                $data = $this->dataForWoo($tcposItem, $match);
                //Product::update($match->_wooId, $data);
                SyncProductUpdate::dispatch($match->_wooId, $data);
                $count_product_update += 1;
                continue;
            } else {
                // Delete it
                //Product::delete($match->_wooId);
                SyncProductDelete::dispatch($match->_wooId, $data);
                $count_product_delete += 1;
                continue;
            }
        }

        // foreach contraire

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_product_found' => $count_product_found,
            'count_product_not_found' => $count_product_not_found,
            'count_product_untouched' => $count_product_untouched,
            'count_product_update' => $count_product_update,
            'count_product_create' => $count_product_create,
            'count_product_delete' => $count_product_delete,
        ]);
    }

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
        if (!data_get($categoryRule, 'manage_stock')) {
            return 'not-managed';
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
        if (!data_get($categoryRule, 'manage_stock')) {
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
        $attributes = [];
        $images = [];
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
        if (isset($wooProduct)) {
            //isset(data_get($wooProduct->data, 'images.0.name'))
            if (data_get($wooProduct->data, 'images.0.name') != $tcposProduct->imageHash) {
                $images = [['name' => $tcposProduct->imageHash, 'src' => $tcposProduct->imageUrl()]];
            }
        }

        return [
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
            'images' => $images,
            'meta_data' => [
                ['key' => config('cdv.wc_meta_tcpos_id'), 'value' => $tcposProduct->_tcposId],
                ['key' => config('cdv.wc_meta_minimum_allowed_quantity'), 'value' => $tcposProduct->minQuantity],
                ['key' => config('cdv.wc_meta_maximum_allowed_quantity'), 'value' => $tcposProduct->maxQuantity],
                ['key' => config('cdv.wc_meta_on_site_price'), 'value' => data_get($tcposProduct->pricesRelations, '1.price')],
            ],
        ];
    }
}
