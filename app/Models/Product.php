<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $with = ['pricesRelations', 'imageRelation', 'stockRelation', 'attributeRelationCellar', 'attributeRelationGrape', 'attributeRelationFillingLevel', 'attributeRelationTownship'];

    /**
     * Get the prices for the product.
     */
    public function pricesRelations()
    {
        return $this->hasMany(Price::class, '_tcpos_product_id', '_tcposId');
    }
    
    /**
     * Get the stock for the product.
     */
    public function stockRelation()
    {
        return $this->hasOne(Stock::class, '_tcpos_product_id', '_tcposId');
    }
    
    /**
     * Get the image for the product.
     */
    public function imageRelation()
    {
        return $this->hasOne(ProductImage::class, '_tcpos_product_id', '_tcposId');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationCellar()
    {
        return $this->hasOne(Attribute::class, '_tcposId', 'groupAId');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationGrape()
    {
        return $this->hasOne(Attribute::class, '_tcposId', 'groupBId');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationFillingLevel()
    {
        return $this->hasOne(Attribute::class, '_tcposId', 'groupCId');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationTownship()
    {
        return $this->hasOne(Attribute::class, '_tcposId', 'groupDId');
    }

    /**
     * Get the stock for the product.
     */
    public function stock()
    {
        return data_get($this->stockRelation, 'value');
    }

    /**
     * Get the attributes for the product.
     */
    public function cellar()
    {
        return [
            'name' => data_get($this->attributeRelationCellar, 'name'),
            'website' => data_get($this->attributeRelationCellar, 'notes1'),
            'email' => data_get($this->attributeRelationCellar, 'notes2'),
            'phone' => data_get($this->attributeRelationCellar, 'notes3'),
            '_tcposId' => data_get($this->attributeRelationCellar, '_tcposId'),
            '_tcposCode' => data_get($this->attributeRelationCellar, '_tcposCode'),
        ];
    }

    /**
     * Get the attributes for the product.
     */
    public function grape()
    {
        return data_get($this->attributeRelationGrape, 'name');
    }

    /**
     * Get the attributes for the product.
     */
    public function fillingLevel()
    {
        return data_get($this->attributeRelationFillingLevel, 'name');
    }

    /**
     * Get the attributes for the product.
     */
    public function township()
    {
        return data_get($this->attributeRelationTownship, 'name');
    }

    /**
     * Get the attributes for the product.
     */
    public function year()
    {
        return $this->notes1;
    }

    /**
     * Get the attributes for the product.
     */
    public function wineType()
    {
        return $this->notes2;
    }

    /**
     * Get the attributes for the product.
     */
    public function spiritType()
    {
        return data_get($this->attributeRelationGrape, 'name');
    }

    /**
     * Get all the attributes for the product.
     */
    public function attributesArray()
    {
        return [
            'year' => in_array($this->category, ['wine', 'cider']) ? $this->year() : null,
            'wineType' => in_array($this->category, ['wine']) ? $this->wineType() : null,
            'grape' => in_array($this->category, ['wine']) ? $this->grape() : null,
            'cellar' => in_array($this->category, ['beer', 'spirit', 'wine', 'cider']) ? $this->cellar() : null,
            'fillingLevel' => in_array($this->category, ['beer', 'spirit', 'wine', 'cider']) ? $this->fillingLevel() : null,
            'township' => in_array($this->category, ['beer', 'spirit', 'wine', 'cider']) ? $this->township() : null,
            'proof' => in_array($this->category, ['spirit', 'beer']) ? $this->notes1 : null,
            'detailUrl' => $this->category == 'selection' ? $this->notes3 : null,
            'mineralDrinkType' => $this->category == 'mineralDrink' ? $this->notes3 : null,
            'bookEditor' => $this->category == 'book' ? $this->notes3 : null,
            'spiritType' => in_array($this->category, ['spirit']) ? $this->spiritType() : null,
        ];
    }

    /**
     * Get the pictures for the product.
     */
    
    public function pictures()
    {
        if (isset($this->imageHash)) {
            return [
                [
                    'id' => $this->_tcposId,
                    'images' => [
                        'url' => $this->imageUrl(),
                        'hash' => md5($this->imageHash),
                    ],
                    'hash' => md5($this->imageHash),
                ]
            ];
        } else {
            return [];
        }
    }

    /**
     * Get the image url for the product.
     */
    
    public function imageUrl()
    {
        if (isset($this->hash)) {
            $path = env('TCPOS_PRODUCTS_IMAGES_BASE_PATH').'/'.$this->_tcposId.'.jpg';
            $url = Storage::disk('public')->url($path);
            return $url;
        } else {
            return null;
        }
    }

    /**
     * Check if need to update.
     */
    
    public function needToUpdate()
    {
        // Check if product price has update
        $prices = $this->pricesRelations;
        $priceToUpdate = false;
        foreach ($prices as $price) {
            $priceToUpdate = $price->sync_action == 'update' ? true : false;
            if ($priceToUpdate == true) {
                break;
            }
        }

        // Check if product stock has update
        $stockToUpdate = data_get($this->stockRelation, 'sync_action') == 'update' ? true : false;

        // Check if product image has update
        $imageToUpdate = data_get($this->imageRelation, 'sync_action') == 'update' ? true : false;

        // Check if product has update
        $productToUpdate = $this->sync_action == 'update' ? true : false;

        // If product, product price, product image or stock price has update
        if ($productToUpdate || $stockToUpdate || $priceToUpdate || $imageToUpdate) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * isStockRuleCorrect.
     */
    public function isStockRuleCorrect()
    {
        $category = $this->category;
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
        if ($this->stock() >= data_get($categoryRule, 'min_stock_quantity')) {
            return true;
        }
    }
}
