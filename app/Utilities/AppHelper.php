<?php
namespace App\Utilities;

use Illuminate\Support\Facades\Http;
use anlutro\LaravelSettings\Facade as Setting;
use Codexshaper\WooCommerce\Facades\Product;
use Codexshaper\WooCommerce\Facades\Order;

class AppHelper
{
    /**
     * Get last tcpos database update.
     */
    public static function getLastTcposUpdate()
    {
        $response = Http::timeout(1000)->get(env('TCPOS_API_WOND_URL').'/getLastRefreshTimestamp')->json();
        $tcposTimestamp = data_get($response, 'getLastRefreshTimestamp.timestamp');
        return \Carbon\Carbon::parse($tcposTimestamp);
    }

    /**
     * Get last tcpos database import.
     */
    public static function getLastTcposImport()
    {
        $date = Setting::get('lastTcposimport', '1900-01-01');
        return \Carbon\Carbon::parse($date);
    }

    /**
     * Get last woo order update.
     */
    public static function getLastWooOrderUpdate()
    {
        $firstOrder = Order::all()->first();
        $firstOrderTimestamp = data_get($firstOrder, 'date_modified');
        return \Carbon\Carbon::parse($firstOrderTimestamp);
    }
}