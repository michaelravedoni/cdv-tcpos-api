<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use romanzipp\QueueMonitor\Models\Monitor;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use anlutro\LaravelSettings\Facade as Setting;
use AppHelper;
use Codexshaper\WooCommerce\Facades\Order;
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
