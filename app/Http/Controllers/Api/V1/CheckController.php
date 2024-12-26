<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class CheckController extends Controller
{
    /**
     * Show API Informations.
     */
    public function woo()
    {
        Artisan::call('check:woo');

        return response()->json([
            'message' => 'Woo Check launched.',
        ]);
    }
}
