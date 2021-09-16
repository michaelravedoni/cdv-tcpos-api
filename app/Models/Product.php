<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'attributes' => 'object',
    ];

    protected $with = ['pricesRelations', 'stockRelation', 'attributeRelationCellar', 'attributeRelationGrape', 'attributeRelationFillingLevel', 'attributeRelationTownship'];

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
     * Get the category for the product.
     */
    public function category()
    {
        if (in_array($this->notes2, ['Rouge', 'Blanc', 'Rosé', 'Mousseux'])) {
            return "wine";
        }
        if (in_array($this->notes2, ['Service du vin'])) {
            return "wineSet";
        }
        if (in_array($this->notes2, ['Bière', 'Bières et Cidres'])) {
            return "beer";
        }
        if (in_array($this->notes2, ['Bières et Cidres', '– Cidre'])) {
            return "cider";
        }
        if (in_array($this->notes2, ['Alcools'])) {
            return "spirit";
        }
        if (in_array($this->notes2, ['Sélection du mois'])) {
            return "selection";
        }
        if (in_array($this->notes2, ['Jus et minérales'])) {
            return "mineralDrink";
        }
        if (in_array($this->notes2, ['Livres'])) {
            return "book";
        }
        return $this->notes2;
    }
}
