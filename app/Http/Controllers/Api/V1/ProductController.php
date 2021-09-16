<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Price;
use App\Http\Resources\ProductResource;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ImportProductImage;
use App\Jobs\ImportProductPrice;

class ProductController extends Controller
{

    /**
     * Show all products.
     */
    public function index()
    {
        return ProductResource::collection(Product::all());
        //return Product::all();
    }

    /**
     * Show the product by id raw.
     */
    public function show($id)
    {
        return Product::where('_tcposId', $id)->get();
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
    public function importPrices($ids = null)
    {

        Price::truncate();

        if ($ids == null) {
            $ids = Product::all()->pluck('_tcposId')->all();
        }

        foreach ($ids as $keyId => $valueId) {
            ImportProductPrice::dispatch($valueId);
        }

        /*
        $collectionIds = collect($ids);
        $collectionIdsChunk = array_chunk($collectionIds->all(), 50);
        $array = [];
        foreach ($collectionIdsChunk as $keyId => $valueId) {
            
            $items = '';
            foreach ($valueId as $key => $value) {
                $items .= '{"article": {"id": '.$value.',"quantity": 1}},';
            }
            $req = Http::get(env('TCPOS_API_WOND_URL').'/getPrice?data={
                "data": {
                    "customerId": 897,
                    "shopId": 1,
                    "date": "2025-11-02T15:23:56",
                    "priceLevelId": 14,
                    "itemList": [
                        '.$items.'
                    ]
                }
            }');
            $response = $req->json();
            $pricesData = data_get($response, 'getPrice.data.itemList');
            $array[] = $pricesData;

        }

        $data = collect($array)->collapse();

        foreach ($data as $keyPrice => $valuePrice) {

            $valuePriceData = (object) data_get($valuePrice, 'article');
            
            $price = new Price;
            $price->_tcpos_product_id = $valuePriceData->id;
            $price->price = $valuePriceData->price;
            $price->discountedprice = $valuePriceData->discountedprice;
            $price->pricelevelid = $valuePriceData->pricelevelid;
            $price->save();
        }
        */

        return response()->json([
            'message' => 'job launched',
            //'data' => $data,
        ]);
    }

    /**
     * Import products in database.
     */
    public function importProducts()
    {
        $begin = microtime(true);

        Product::truncate();

        foreach ($this->getProducts() as $key => $productRaw) {

            $product = (object) $productRaw;

            $productCreate = new Product;
            $productCreate->name = $product->description;
            $productCreate->minQuantity = config('cdv.default_product_min_quantity');
            $productCreate->maxQuantity = $product->articleOrder ?? config('cdv.default_product_max_quantity');
            $productCreate->weight = $product->preparationWeight ?? 0;

            $productCreate->stockQty = null;
            $productCreate->category = null;
            $productCreate->pictures = null;
            $productCreate->attributes = null;
            
            $productCreate->vatInPercent = data_get($product, 'vats.vatindex1', 'vats.vatindex2');
            $productCreate->measureUnitId = $product->measureUnitId;
            $productCreate->printoutNotes = $product->printoutNotes;
            $productCreate->notes1 = $product->notes1;
            $productCreate->notes2 = $product->notes2;
            $productCreate->notes2 = $product->notes2;
            $productCreate->groupAId = $product->groupAId;
            $productCreate->groupBId = $product->groupBId;
            $productCreate->groupCId = $product->groupCId;
            $productCreate->groupDId = $product->groupDId;
            $productCreate->groupEId = $product->groupEId;
            $productCreate->groupFId = $product->groupFId;

            $productCreate->_tcposId = $product->id;
            $productCreate->_tcposCode = $product->code;
            $productCreate->save();

            if ($key == 2) {
                $brake;
            }
        }
        
        $end = microtime(true) - $begin;

        return response()->json([
            'message' => 'imported',
            'time' => $end,
            'count' => Product::all()->count(),
        ]);
    }

    /**
     * Import product image from database.
     */
    public function importImage($id)
    {
        $req = Http::get(env('TCPOS_API_WOND_URL').'/getImage?id='.$id);
        $response = $req->json();
        $data = data_get($response, 'getImage.imageList.0.bitmapFile');

        //$image = 'data:image/jpg;base64,'.$data;
        $imageDecode = base64_decode(chunk_split($data));
        $path = '/products/photo.jpg';
        $store = Storage::disk('public')->put('/products/'.$valueId.'.jpg', $imageDecode);
        $url = Storage::disk('public')->url($path);

        return response()->json([
            'message' => 'ok',
            'url' => $url,
        ]);
    }

    /**
     * Import products images from database.
     */
    public function importImages()
    {
        $ids = Product::all()->pluck('_tcposId')->all();

        foreach ($ids as $keyId => $valueId) {
            ImportProductImage::dispatch($valueId);
        }

        return response()->json([
            'message' => 'imported',
            'time' => null,
            'count' => null,
        ]);
    }
}
