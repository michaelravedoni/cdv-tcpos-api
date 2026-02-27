<?php

namespace App\Utilities;

use anlutro\LaravelSettings\Facade as Setting;
use Codexshaper\WooCommerce\Facades\Order;
use Illuminate\Support\Facades\Http;

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

    /**
     * Check last tcpos database update and check if need import.
     */
    public static function needImportFromTcpos()
    {
        $tcposTimestamp = Self::getLastTcposUpdate()->toDateTimeLocalString();
        $localTimestamp = Setting::get('lastTcposUpdate', null);

        if ($tcposTimestamp <= $localTimestamp) {
            return false;
        }
        if ($tcposTimestamp > $localTimestamp) {
            return true;
        }

    }

    /**
     * Check last woo database orders update and check if need import.
     */
    public static function needOrdersImportFromWoo()
    {
        $localTimestamp = Setting::get('lastWooUpdate', null);
        if (isset($localTimestamp)) {
            $orders = Order::all(['after' => $localTimestamp]);
            $ordersCount = $orders->count();
            if ($ordersCount > 0) {
                return true;
            } else {
                return false;
            }
        } else {
            $firstOrder = Order::all()->first();
            $firstOrderTimestamp = data_get($firstOrder, 'date_modified');
            Setting::set('lastWooUpdate', $firstOrderTimestamp);
            Setting::save();

            return true;
        }
    }

    /**
     * Get Metadata value from Woo resource
     */
    public static function getMetadataValueFromKey($metadataArray, $key)
    {
        if (empty($metadataArray) || ! is_array($metadataArray)) {
            return null;
        }

        return data_get($metadataArray, array_search($key, array_column($metadataArray, 'key')).'.value');
    }
}
