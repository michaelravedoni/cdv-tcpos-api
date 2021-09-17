<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Product;

class InfoController extends Controller
{
    /**
     * Show Informations.
     */
    public function show()
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
                'beer' => Product::where('category', 'beer')->count(),
                'book' => Product::where('category', 'book')->count(),
                'selection' => Product::where('category', 'selection')->count(),
                'none' => Product::where('category', 'none')->count(),
            ],
        ]);
    }
}
