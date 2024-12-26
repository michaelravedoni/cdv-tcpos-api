<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class VoucherController extends Controller
{
    /**
     * Show all vouchers.
     */
    public function getVouchers()
    {
        $response = Http::get(env('TCPOS_API_WOND_URL').'/getVouchers')->json();
        $data = data_get($response, 'getVouchers.voucherList');

        return $data;
    }

    /**
     * Get the voucher.
     */
    public function getVoucher($id)
    {
        $response = Http::get(env('TCPOS_API_CDV_CUSTOM_URL').'/getvoucher/barcode/'.$id)->json();
        $data = $response;

        return $data;
    }
}
