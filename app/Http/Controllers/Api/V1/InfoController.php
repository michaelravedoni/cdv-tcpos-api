<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class InfoController extends Controller
{
    /**
     * Show API Informations.
     */
    public function api(): JsonResponse
    {

        return response()->json([
            'message' => 'Informations',
            'products_count' => Product::all()->count(),
            'products_where_minimal_quantity_<_6' => Product::whereHas('stockRelation', function (Builder $query) {
                $query->where('value', '<', 6);
            })->count(),
            'products_where_minimal_quantity_>=_6' => Product::whereHas('stockRelation', function (Builder $query) {
                $query->where('value', '>=', 6);
            })->count(),
            'count_by_category' => [
                'count_wine' => Product::where('category', 'wine')->count(),
                'spirit' => Product::where('category', 'spirit')->count(),
                'cider' => Product::where('category', 'cider')->count(),
                'wineSet' => Product::where('category', 'wineSet')->count(),
                'mineralDrink' => Product::where('category', 'mineralDrink')->count(),
                'regionalProduct' => Product::where('category', 'regionalProduct')->count(),
                'beer' => Product::where('category', 'beer')->count(),
                'book' => Product::where('category', 'book')->count(),
                'selection' => Product::where('category', 'selection')->count(),
                'none' => Product::where('category', 'none')->count(),
            ],
        ]);
    }
}
