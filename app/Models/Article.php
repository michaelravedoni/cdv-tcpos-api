<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected $with = ['attributeRelationCellar', 'attributeRelationGrape', 'attributeRelationFillingLevel', 'attributeRelationTownship'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'priceLevelCodes' => 'object',
    ];

    /**
     * Get the TCPOS product for the article.
     */
    public function tcposProduct()
    {
        return $this->hasOne(Product::class, '_tcposCode', '_tcposCode');
    }

    /**
     * Get the prices for the product.
     */
    public function pricesRelations()
    {
        return $this->hasMany(Price::class, '_tcpos_product_id', '_tcposId');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationCellar()
    {
        return $this->hasOne(Attribute::class, '_tcposCode', 'groupACode');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationGrape()
    {
        return $this->hasOne(Attribute::class, '_tcposCode', 'groupBCode');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationFillingLevel()
    {
        return $this->hasOne(Attribute::class, '_tcposCode', 'groupCCode');
    }

    /**
     * Get the attributes for the product.
     */
    public function attributeRelationTownship()
    {
        return $this->hasOne(Attribute::class, '_tcposCode', 'groupDCode');
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
     * Set article category.
     */
    public function setArticleCategory($notes2)
    {
        if (in_array($notes2, ['Rouge', 'Blanc', 'Rosé', 'Mousseux'])) {
            return 'wine';
        }
        if (in_array($notes2, ['Service du vin'])) {
            return 'wineSet';
        }
        if (in_array($notes2, ['Bière', 'Bières et Cidres'])) {
            return 'beer';
        }
        if (in_array($notes2, ['Bières et Cidres', '– Cidre'])) {
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
