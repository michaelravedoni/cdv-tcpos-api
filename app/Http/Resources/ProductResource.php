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
            'description' => $this->description,
            'prices' => [
                'takeAway' => [
                    'articleCode' => $this->_tcposCode,
                    'priceLevelCode' => 2, // takeAway = code 2 (http://tcposvsrv06.tcpos.com:10308/getPricelevels)
                    'price' => data_get($this->pricesRelations, '0.price'),
                    'vatInPercent' => $this->vatInPercent,
                ],
                'onSite' => [
                    'articleCode' => $this->_tcposCode,
                    'priceLevelCode' => 5,  // onSite = code 2 (http://tcposvsrv06.tcpos.com:10308/getPricelevels)
                    'price' => data_get($this->pricesRelations, '1.price'),
                    'vatInPercent' => $this->vatInPercent,
                ],
                'online' => [
                    'articleCode' => $this->_tcposCode,
                    'priceLevelCode' => 13,  // online = code 2 (http://tcposvsrv06.tcpos.com:10308/getPricelevels)
                    'price' => data_get($this->pricesRelations, '2.price'),
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
            'attributes' => $this->attributesArray(),
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
