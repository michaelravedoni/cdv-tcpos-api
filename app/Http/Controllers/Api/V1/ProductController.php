<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Jobs\ImportProductImage;
use App\Jobs\ImportProductPrice;
use App\Models\Price;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Show all products.
     */
    public function index()
    {
        return ProductResource::collection(Product::where('category', '<>', 'none')->get());
        //return Product::all();
    }

    /**
     * Show the product by id raw.
     */
    public function getById($id)
    {
        return new ProductResource(Product::where('_tcposId', $id)->first());
        //return Product::where('_tcposId', $id)->first();
    }

    /**
     * Show the product by code raw.
     */
    public function getByCode($id)
    {
        return new ProductResource(Product::where('_tcposCode', $id)->first());
    }

    /**
     * Show the products raw.
     */
    public function getProducts()
    {
        $response = Http::timeout(1000)->get(env('TCPOS_API_WOND_URL').'/getArticles')->json();
        $data = data_get($response, 'getArticles.articleList');

        return $data;
    }

    /**
     * Filter products by category.
     */
    public function indexByCategory($category = 'wine')
    {
        return ProductResource::collection(Product::where('category', $category)->get());
    }

    /**
     * Get product price.
     */
    public function getPrice($id)
    {
        $req = Http::get(env('TCPOS_API_WOND_URL').'/getPrice?data={
            "data": {
                "shopId": 1,
                "priceLevelId": 14,
                "itemList": [{
                    "article": {
                    "priceLevelId": 14,
                        "id": '.$id.',
                        "quantity": 1
                    }
                },
                {
                    "article": {
                    "priceLevelId": 6,
                        "id": '.$id.',
                        "quantity": 1
                    }
                }]
            }
        }');
        $response = $req->json();
        $data = data_get($response, 'getPrice.data.itemList');

        $array = [];
        foreach ($data as $key => $value) {
            $priceItem = (object) data_get($value, 'article');
            $array[] = $priceItem;
        }

        return $array;
    }

    /**
     * Show all prices.
     */
    public function indexPrices()
    {
        $data = Price::all();

        return $data;
    }

    /**
     * Get products prices.
     */
    public function importPrices($ids = null): JsonResponse
    {
        //Price::truncate();

        if ($ids == null) {
            $ids = Product::all()->pluck('_tcposId')->all();
        }

        foreach ($ids as $keyId => $valueId) {
            ImportProductPrice::dispatch($valueId);
        }

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'start', 'resource' => 'prices'])->log('Import products prices from tcpos database. See /jobs');

        return response()->json([
            'message' => 'job launched. See /jobs',
        ]);
    }

    /**
     * Import products in database.
     */
    public function importProducts(): JsonResponse
    {
        $begin = microtime(true);

        $localProducts = $this->index();
        $tcposProducts = $this->getProducts();
        $updateOrNoneLocalProductsIds = [];
        $countToUpdateProducts = 0;
        $countToCreateProducts = 0;
        $countToNoneProducts = 0;

        foreach ($tcposProducts as $tcposProduct) {
            $tcposProductHash = md5(json_encode($tcposProduct));
            $localProduct = Product::where('_tcposId', data_get($tcposProduct, 'id'))->first();

            // If a product in database exists
            if (isset($localProduct)) {

                // Store the id on an array
                $updateOrNoneLocalProductsIds[] = $localProduct->id;

                // If the hash is the same as one in the database
                if ($tcposProductHash == $localProduct->hash) {
                    $localProduct->sync_action = 'none';
                    $localProduct->save();

                    // Increment the counter
                    $countToNoneProducts += 1;
                } else {
                    // If the hash is not the same and the product has to be updated : update it
                    $product = (object) $tcposProduct;

                    $localProduct->sync_action = 'update';
                    $localProduct->hash = $tcposProductHash;

                    $localProduct->name = $product->description;
                    $localProduct->category = $this->getProductCategory($product->notes2, $product->notes3);
                    $localProduct->minQuantity = config('cdv.default_product_min_quantity');
                    $localProduct->maxQuantity = $product->articleOrder ?? config('cdv.default_product_max_quantity');

                    $localProduct->weight = $product->preparationWeight ?? 0;
                    $localProduct->vatInPercent = data_get($product, 'vats.vatindex1', 'vats.vatindex2');

                    $localProduct->description = $product->wondDescription;
                    $localProduct->articleOrder = $product->articleOrder;
                    $localProduct->isAddition = $product->isAddition;
                    $localProduct->measureUnitId = $product->measureUnitId;
                    $localProduct->printoutNotes = $product->printoutNotes;
                    $localProduct->notes1 = $product->notes1;
                    $localProduct->notes2 = $product->notes2;
                    $localProduct->notes3 = $product->notes3;
                    $localProduct->groupAId = $product->groupAId;
                    $localProduct->groupBId = $product->groupBId;
                    $localProduct->groupCId = $product->groupCId;
                    $localProduct->groupDId = $product->groupDId;
                    $localProduct->groupEId = $product->groupEId;
                    $localProduct->groupFId = $product->groupFId;

                    $localProduct->save();

                    // Increment the counter
                    $countToUpdateProducts += 1;
                }
            } else {
                // If the product not exists in database: create it
                $product = (object) $tcposProduct;

                $productCreate = new Product;
                $productCreate->name = $product->description;
                $productCreate->category = $this->getProductCategory($product->notes2, $product->notes3);
                $productCreate->minQuantity = config('cdv.default_product_min_quantity');
                $productCreate->maxQuantity = $product->articleOrder ?? config('cdv.default_product_max_quantity');

                $productCreate->weight = $product->preparationWeight ?? 0;
                $productCreate->vatInPercent = data_get($product, 'vats.vatindex1', 'vats.vatindex2');

                $productCreate->sync_action = 'update';
                $productCreate->hash = $tcposProductHash;

                $productCreate->description = $product->wondDescription;
                $productCreate->articleOrder = $product->articleOrder;
                $productCreate->isAddition = $product->isAddition;
                $productCreate->measureUnitId = $product->measureUnitId;
                $productCreate->printoutNotes = $product->printoutNotes;
                $productCreate->notes1 = $product->notes1;
                $productCreate->notes2 = $product->notes2;
                $productCreate->notes3 = $product->notes3;
                $productCreate->groupAId = $product->groupAId;
                $productCreate->groupBId = $product->groupBId;
                $productCreate->groupCId = $product->groupCId;
                $productCreate->groupDId = $product->groupDId;
                $productCreate->groupEId = $product->groupEId;
                $productCreate->groupFId = $product->groupFId;

                $productCreate->_tcposId = $product->id;
                $productCreate->_tcposCode = $product->code;
                $productCreate->save();

                // Store the id on an array
                $updateOrNoneLocalProductsIds[] = $productCreate->id;

                // Increment the counter
                $countToCreateProducts += 1;
            }
        }

        // Delete products not in the list
        $countLocalProductsToDelete = Product::whereNotIn('id', $updateOrNoneLocalProductsIds)->get()->count();
        $localProductsToDelete = Product::whereNotIn('id', $updateOrNoneLocalProductsIds)->delete();

        $end = microtime(true) - $begin;

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'end', 'resource' => 'products', 'duration' => $end])->log(Product::all()->count().' products imported from TCPOS | '.$countToNoneProducts.' to untouch, '.$countToCreateProducts.' to create, '.$countToUpdateProducts.' to update and '.$countLocalProductsToDelete.' deleted.');

        return response()->json([
            'message' => 'imported',
            'time' => $end,
        ]);
    }

    /**
     * Import product image from database.
     */
    public function importImage($id): JsonResponse
    {

        $req = Http::get(env('TCPOS_API_WOND_URL').'/getImage?id='.$id);
        $response = $req->json();
        $data = data_get($response, 'getImage.imageList.0.bitmapFile');

        $productImage = ProductImage::where('_tcpos_product_id', $id)->first();
        if (empty($productImage)) {
            $productImage = new ProductImage;
            $productImage->_tcpos_product_id = $id;
        }

        if (empty($data)) {
            //No image found
            $productImage->hash = null;
            $productImage->sync_action = 'none';
            $productImage->save();

            return response()->json([
                'message' => 'Image not found in tcpos',
            ]);
        }

        if ($productImage->hash == md5($data)) {
            $productImage->sync_action = 'none';
            $productImage->save();

            return response()->json([
                'message' => 'Image already saved',
            ]);
        }

        $image = $data;
        $image = str_replace(' ', '+', $image);
        $imageDecode = base64_decode($image);
        $path = env('TCPOS_PRODUCTS_IMAGES_BASE_PATH').'/'.$id.'.jpg';
        Storage::disk('public')->put($path, $imageDecode);

        $productImage->hash = md5($data);
        $productImage->sync_action = 'update';
        $productImage->save();

        return response()->json([
            'message' => 'Image saved',
        ]);
    }

    /**
     * Import products images from database.
     */
    public function importImages(): JsonResponse
    {
        $ids = Product::all()->pluck('_tcposId')->all();

        foreach ($ids as $keyId => $valueId) {
            ImportProductImage::dispatch($valueId);
        }

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'job', 'resource' => 'images'])->log('Products images import from TCPOS queued');

        return response()->json([
            'message' => 'job launched. See /jobs',
        ]);
    }

    /**
     * Set product category.
     */
    public function getProductCategory($notes2, $notes3)
    {
        if (in_array($notes2, ['Rouge', 'Blanc', 'Rosé', 'Mousseux'])) {
            return 'wine';
        }
        if (in_array($notes2, ['Service du vin'])) {
            return 'wineSet';
        }
        if (in_array($notes2, ['Bières et Cidres']) && in_array($notes3, ['Bière'])) {
            return 'beer';
        }
        if (in_array($notes2, ['Bières et Cidres'])) {
            return 'cider';
        }
        if (in_array($notes2, ['Alcools'])) {
            return 'spirit';
        }
        if (in_array($notes2, ['Sélection du mois'])) {
            return 'selection';
        }
        if (in_array($notes2, ['Jus et minérales'])) {
            return 'mineralDrink';
        }
        if (in_array($notes2, ['Produits du terroir'])) {
            return 'regionalProduct';
        }
        if (in_array($notes2, ['Livres'])) {
            return 'book';
        }

        return 'none';
    }
}
