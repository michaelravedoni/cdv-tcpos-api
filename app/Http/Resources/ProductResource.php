<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'prices' => [
                'takeAway' => [
                    'articleCode' => $this->_tcposCode,
                    'priceLevelCode' => data_get($this->pricesRelations, '0.pricelevelid'),
                    'price' => data_get($this->pricesRelations, '0.price'),
                    'vatInPercent' => $this->vatInPercent,
                ]
            ],
            'pictures' => [
                'url' => Storage::disk('public')->url('products/'.$this->_tcposId.'.jpg'),
            ],
            'attributes' => [
                'year' => $this->year(),
                'wineType' => $this->wineType(),
                'grape' => $this->grape(),
                'cellar' => $this->cellar(),
                'fillingLevel' => $this->fillingLevel(),
                'township' => $this->township(),
            ],
            'minQuantity' => $this->minQuantity,
            'maxQuantity' => $this->maxQuantity,
            'stockQty' => $this->stockQty,
            'category' => null,
            'weight' => $this->weight,
            '_tcposCode' => $this->_tcposCode,
            '_tcposId' => $this->_tcposId,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
