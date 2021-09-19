<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Product;
use App\Models\Price;
use App\Models\Attribute;

class AttributeController extends Controller
{

    /**
     * Show all attributes.
     */
    public function index()
    {
        return Attribute::all();
    }

    /**
     * Show the attribute by id raw.
     */
    public function show($id)
    {
        return Attribute::where('_tcposId', $id)->get();
    }

    /**
     * Import attributes in database.
     */
    public function importAttributes()
    {
        $begin = microtime(true);

        Attribute::truncate();
        activity()->log('Import: All attributes truncated from database');

        foreach ($this->getAttributes() as $key => $attributeRaw) {

            $attribute = (object) $attributeRaw;

            $attributeCreate = new Attribute;
            $attributeCreate->name = $attribute->DESCRIPTION;
            $attributeCreate->notes1 = $attribute->NOTES1;
            $attributeCreate->notes2 = $attribute->NOTES2;
            $attributeCreate->notes3 = $attribute->NOTES3;
            $attributeCreate->notes = [$attribute->NOTES3, $attribute->NOTES2, $attribute->NOTES3];
            $attributeCreate->_tcposId = $attribute->ID;
            $attributeCreate->_tcposCode = $attribute->CODE;
            $attributeCreate->save();

            /*if ($key == 2) {
                $brake;
            }*/
        }
        
        $end = microtime(true) - $begin;

        activity()->withProperties(['duration' => $end])->log('Import: '.Attribute::all()->count().' attributes imported from tcpos database');

        return response()->json([
            'message' => 'imported',
            'time' => $end,
            'count' => Attribute::all()->count(),
        ]);
    }

    /**
     * Show the attributes raw.
     */
    public function getAttributes()
    {
        $req = Http::withOptions([
            'verify' => false,
        ])->get(env('TCPOS_API_CDV_URL').'/getallgroups');
        $response = $req->json();
        $data = data_get($response, 'GROUPS');

        return $data;
    }
}
