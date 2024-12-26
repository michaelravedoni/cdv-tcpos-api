<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class CheckController extends Controller
{
    /**
     * Show API Informations.
     */
    public function woo(): JsonResponse
    {
        Artisan::call('check:woo');

        return response()->json([
            'message' => 'Woo Check launched.',
        ]);
    }
}
