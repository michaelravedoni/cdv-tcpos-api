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
                ],
                'onSite' => [
                    'articleCode' => $this->_tcposCode,
                    'priceLevelCode' => data_get($this->pricesRelations, '1.pricelevelid'),
                    'price' => data_get($this->pricesRelations, '1.price'),
                    'vatInPercent' => $this->vatInPercent,
                ]
            ],
            'pictures' => [
                [
                    'id' => $this->_tcposId,
                    'images' => [
                        'url' => $this->imageUrl(),
                        'hash' => md5($this->imageHash),
                    ],
                    'hash' => null,
                ]
            ],
            'attributes' => [
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
            ],
            'minQuantity' => $this->minQuantity,
            'maxQuantity' => $this->maxQuantity,
            'stockQty' => $this->stock(),
            'category' => $this->category,
            'weight' => $this->weight,
            '_tcposCode' => $this->_tcposCode,
            '_tcposId' => $this->_tcposId,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
