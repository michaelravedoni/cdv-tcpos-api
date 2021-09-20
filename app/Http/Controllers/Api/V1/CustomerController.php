<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    /**
     * Get customer.
     */
    public function getCustomer($cardnum)
    {
        $response = Http::withOptions(['verify' => false,])->get(env('TCPOS_API_CDV_URL').'/getCustomerDetails/card/'.$cardnum)->json();
        $data = data_get($response, 'USER');
        return response()->json([
            'firstName' => data_get($data, 'FIRST_NAME') ?? data_get(explode(" ", data_get($data, 'DESCRIPTION')), '0'),
            'lastName' => data_get($data, 'NAME') ?? data_get(explode(" ", data_get($data, 'DESCRIPTION')), '1'),
            'email' => data_get($data, 'EMAIL'),
            'phone' => data_get($data, 'PHONE'),
            'title' => data_get($data, 'TITLE'),
            'address' => [
                'firstName' => data_get($data, 'FIRST_NAME') ?? data_get(explode(" ", data_get($data, 'DESCRIPTION')), '0'),
                'lastName' => data_get($data, 'NAME') ?? data_get(explode(" ", data_get($data, 'DESCRIPTION')), '1'),
                'company' => data_get($data, 'NOTES1'),
                'url' => data_get($data, 'URL'),
                'address' => [data_get($data, 'STREET')],
                'city' => data_get($data, 'CITY'),
                'zipcode' => data_get($data, 'ZIP_CODE'),
                'country' => data_get($data, 'NATION'),
            ],
            'accountFunds' => $this->getCustomerFunds($cardnum),
            'accountType' => in_array(data_get($data, 'COMPANY_ID'), [2, 3]) ? 'chatelin' : 'customer',
            'prepayBalanceCash' => data_get($data, 'PREPAY_BALANCE_CASH'),
            'validFrom' => data_get($data, 'VALID_FROM'),
            '_tcposId' => data_get($data, 'ID'),
            '_tcposCardnumber' => data_get($data, 'CARD_NUM'),
            'raw' => $data,
        ]);
    }

    /**
     * Get the funds customer by card number.
     */
    public function getCustomerFunds($cardnum)
    {
        $response = Http::withOptions(['verify' => false,])->get(env('TCPOS_API_CDV_URL').'/getcustomersolde/cardnum/'.$cardnum)->json();
        $data = data_get($response, 'SOLDE');;
        return $data;
    }

    /**
     * Get the customer by card number.
     */
    public function getCustomerByCardnum(Request $request, $cardnum)
    {
        if (!$request->hasHeader('x-access-secret') && $request->header('X-Header-Name') != env('TCPOS_API_SECRET')) {
            return response()->json(['message' => 'Access refused']);
        }

        return $this->getCustomer($cardnum);
    }

    /**
     * Get the funds customer by card number.
     */
    public function getCustomerFundsByCardnumber(Request $request, $cardnum)
    {
        if (!$request->hasHeader('x-access-secret') && $request->header('X-Header-Name') != env('TCPOS_API_SECRET')) {
            return response()->json(['message' => 'Access refused']);
        }
        return $this->getCustomerFunds($cardnum);
    }

    /**
     * Get verification field.
     */
    public function getCustomerVerificationField($cardnum)
    {
        return response()->json(['zipcode']);
    }

    /**
     * Verify user.
     */
    public function verifyCustomer(Request $request, $cardnum)
    {
        $verificationInput = $request->input('verificationFields');
        $value = data_get($verificationInput, '0.value');
        if ($value == data_get($this->getCustomer($cardnum), 'zip')) {
            return response()->json(true, 200);
        }
        return response()->json('Verification fields are incorrect', 401);
        
    }
}
