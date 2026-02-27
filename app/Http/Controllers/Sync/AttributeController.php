<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Jobs\SyncAttributeTermUpdate;
use App\Jobs\SyncAttributeUpdate;
use App\Models\Attribute as TcposAttribute;
use Codexshaper\WooCommerce\Facades\Attribute;
use Codexshaper\WooCommerce\Facades\Term;
use Illuminate\Http\JsonResponse;

class AttributeController extends Controller
{
    /**
     * Get all woo attributes.
     */
    public function getWooAttributes()
    {
        $attributes1 = Attribute::all(['per_page' => 100, 'page' => 1]);
        $attributes2 = Attribute::all(['per_page' => 100, 'page' => 2]);
        $attributes3 = Attribute::all(['per_page' => 100, 'page' => 3]);

        return $attributes1->merge($attributes2)->merge($attributes3);
    }

    /**
     * Get all woo cellar terms.
     */
    public function getWooCellarTerms()
    {
        $terms1 = Term::all(config('cdv.wc_attribute_ids.cellar'), ['per_page' => 100, 'page' => 1]);
        $terms2 = Term::all(config('cdv.wc_attribute_ids.cellar'), ['per_page' => 100, 'page' => 2]);
        $terms3 = Term::all(config('cdv.wc_attribute_ids.cellar'), ['per_page' => 100, 'page' => 3]);

        return $terms1->merge($terms2)->merge($terms3);
    }

    /**
     * Get all tcpos attributes.
     */
    public function getTcposAttributes()
    {
        return TcposAttribute::all();
    }

    /**
     * Sync attributes.
     */
    public function sync(): JsonResponse
    {
        $count_attribute_update = 0;
        foreach ($this->getWooAttributes() as $wooAttributes) {
            $attribute_id = $wooAttributes->id;
            $data = [
                'has_archives' => true,
            ];
            //Attribute::update($attribute_id, $data);
            SyncAttributeUpdate::dispatch($attribute_id, $data);
            $count_attribute_update += 1;
        }

        $count_term_update = 0;
        $count_term_not_found = 0;
        foreach ($this->getWooCellarTerms() as $wooCellarTerm) {
            $woo_cellar_term_id = $wooCellarTerm->id;
            $woo_cellar_term_name = $wooCellarTerm->name;

            $tcposAttribute = TcposAttribute::where('name', str_replace('&amp;', '&', $woo_cellar_term_name))->first();

            if ($tcposAttribute) {
                $data = [
                    'meta_data' => [
                        ['key' => 'website', 'value' => $tcposAttribute->notes1],
                        ['key' => 'email', 'value' => $tcposAttribute->notes2],
                        ['key' => 'phone', 'value' => $tcposAttribute->notes3],
                    ],
                ];
                //Term::update(config('cdv.wc_attribute_ids.cellar'), $woo_cellar_term_id, $data);
                SyncAttributeTermUpdate::dispatch($woo_cellar_term_id, $data);
                $count_term_update += 1;
            } else {
                $count_term_not_found += 1;
            }
        }

        activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'attributes'])->log('Attributes sync queued |  '.$count_term_update.' update and '.$count_term_not_found.' not found');

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_term_update' => $count_term_update,
            'count_term_not_found' => $count_term_not_found,
        ]);
    }
}
