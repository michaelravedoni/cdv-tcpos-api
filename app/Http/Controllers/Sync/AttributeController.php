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
        return $this->fetchAllPages(fn (int $page, int $perPage) => Attribute::all(['per_page' => $perPage, 'page' => $page]));
    }

    /**
     * Get all woo cellar terms.
     */
    public function getWooCellarTerms()
    {
        $cellarId = config('cdv.wc_attribute_ids.cellar');

        return $this->fetchAllPages(fn (int $page, int $perPage) => Term::all($cellarId, ['per_page' => $perPage, 'page' => $page]));
    }

    /**
     * Paginate a WooCommerce collection dynamically until an incomplete or empty page is returned.
     */
    private function fetchAllPages(callable $fetcher, int $perPage = 100)
    {
        $all = collect();
        $page = 1;

        do {
            $batch = $fetcher($page, $perPage);
            if ($batch->isEmpty()) {
                break;
            }
            $all = $all->merge($batch);
            $page++;
        } while ($batch->count() === $perPage);

        return $all;
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
        foreach ($this->getWooAttributes() as $wooAttribute) {
            if (data_get($wooAttribute, 'has_archives') === true) {
                continue;
            }

            SyncAttributeUpdate::dispatch($wooAttribute->id, ['has_archives' => true]);
            $count_attribute_update += 1;
        }

        $tcposAttributesByName = TcposAttribute::all()->keyBy('name');
        $count_term_update = 0;
        $count_term_unchanged = 0;
        $count_term_not_found = 0;

        foreach ($this->getWooCellarTerms() as $wooCellarTerm) {
            $woo_cellar_term_id = $wooCellarTerm->id;
            $woo_cellar_term_name = str_replace('&amp;', '&', $wooCellarTerm->name);

            $tcposAttribute = $tcposAttributesByName->get($woo_cellar_term_name);

            if (! $tcposAttribute) {
                $count_term_not_found += 1;

                continue;
            }

            $meta = collect(data_get($wooCellarTerm, 'meta_data', []));
            $currentWebsite = data_get($meta->firstWhere('key', 'website'), 'value');
            $currentEmail = data_get($meta->firstWhere('key', 'email'), 'value');
            $currentPhone = data_get($meta->firstWhere('key', 'phone'), 'value');

            if ($currentWebsite === $tcposAttribute->notes1
                && $currentEmail === $tcposAttribute->notes2
                && $currentPhone === $tcposAttribute->notes3) {
                $count_term_unchanged += 1;

                continue;
            }

            $data = [
                'meta_data' => [
                    ['key' => 'website', 'value' => $tcposAttribute->notes1],
                    ['key' => 'email', 'value' => $tcposAttribute->notes2],
                    ['key' => 'phone', 'value' => $tcposAttribute->notes3],
                ],
            ];
            SyncAttributeTermUpdate::dispatch($woo_cellar_term_id, $data);
            $count_term_update += 1;
        }

        activity()->withProperties(['group' => 'sync', 'level' => 'job', 'resource' => 'attributes'])->log('Attributes sync queued | '.$count_attribute_update.' attributes, '.$count_term_update.' terms updated, '.$count_term_unchanged.' unchanged, '.$count_term_not_found.' not found');

        return response()->json([
            'message' => 'Sync queued. See /jobs.',
            'count_attribute_update' => $count_attribute_update,
            'count_term_update' => $count_term_update,
            'count_term_unchanged' => $count_term_unchanged,
            'count_term_not_found' => $count_term_not_found,
        ]);
    }
}
