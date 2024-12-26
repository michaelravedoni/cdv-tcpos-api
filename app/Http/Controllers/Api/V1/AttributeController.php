<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

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
    public function importAttributes(): JsonResponse
    {
        $begin = microtime(true);

        Attribute::truncate();
        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'info', 'resource' => 'attributes'])->log('Attributes truncated in local database');

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

        activity()->withProperties(['group' => 'import-tcpos', 'level' => 'end', 'resource' => 'attributes', 'duration' => $end])->log(Attribute::all()->count().' attributes imported from TCPOS');

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
