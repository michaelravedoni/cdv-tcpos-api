<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{
    /**
     * Get customer.
     */
    public function getCustomer($cardnum): JsonResponse
    {
        $requestToken = Http::get(env('TCPOS_API_WOND_URL').'/login?user='.env('TCPOS_API_WOND_USER').'&password='.env('TCPOS_API_WOND_PASSWORD'));
        $token = data_get($requestToken->json(), 'login.customerProperties.token', false);

        $responseSearchCustomer = Http::get(env('TCPOS_API_WOND_URL').'/searchCustomerByData?cardNum='.$cardnum.'&token='.urlencode($token))->json();
        $idSearchCustomer = data_get($responseSearchCustomer, 'searchCustomerByData.id');
        if (isset($idSearchCustomer)) {
            $responseGetCustomer = Http::get(env('TCPOS_API_WOND_URL').'/getCustomer?email&id='.$idSearchCustomer.'&token='.urlencode($token))->json();
            $data = data_get($responseGetCustomer, 'getCustomer.customer');

            return response()->json([
                'firstName' => data_get($data, 'firstName') ?? data_get(explode(' ', data_get($data, 'description')), '1'),
                'lastName' => data_get($data, 'lastName') ?? data_get(explode(' ', data_get($data, 'description')), '0'),
                'email' => data_get($data, 'email'),
                'phone' => data_get($data, 'phone'),
                'title' => data_get($data, 'title'),
                'address' => [
                    'firstName' => data_get($data, 'firstName') ?? data_get(explode(' ', data_get($data, 'description')), '1'),
                    'lastName' => data_get($data, 'lastName') ?? data_get(explode(' ', data_get($data, 'description')), '0'),
                    'company' => data_get($data, 'notes1'),
                    'url' => data_get($data, 'url'),
                    'address' => [data_get($data, 'street')],
                    'city' => data_get($data, 'city'),
                    'zipcode' => data_get($data, 'zipCode'),
                    'country' => data_get($data, 'country', data_get($data, 'state')),
                ],
                'cardnum' => data_get($data, 'cardNum'),
                'cardType' => data_get($data, 'cardType'),
                'accountFunds' => data_get($data, 'prepayBalanceCash'),
                'prepayBalanceCash' => data_get($data, 'prepayBalanceCash'),
                'accountType' => in_array(data_get($data, 'COMPANY_ID'), [2, 3]) ? 'chatelin' : 'customer',
                'validFrom' => data_get($data, 'validFrom'),
                'validTo' => data_get($data, 'validTo'),
                '_tcposId' => data_get($data, 'ID'),
                '_tcposCardnumber' => data_get($data, 'CARD_NUM'),
                //'raw' => $data,
            ]);
        }
        return response()->json(['code' => '404', 'message' => 'Customer not found.'], 404);
    }

    /**
     * Get the funds customer by card number.
     */
    public function getCustomerFunds($cardnum)
    {
        return data_get($this->getCustomer($cardnum), 'original.accountFunds');
    }

    /**
     * Get the customer by card number.
     */
    public function getCustomerByCardnum(Request $request, $cardnum)
    {
        if (! $request->hasHeader('x-access-secret') && $request->header('X-Header-Name') != env('TCPOS_API_SECRET')) {
            return response()->json(['message' => 'Access refused']);
        }

        return $this->getCustomer($cardnum);
    }

    /**
     * Get the funds customer by card number.
     */
    public function getCustomerFundsByCardnumber(Request $request, $cardnum)
    {
        if (! $request->hasHeader('x-access-secret') && $request->header('X-Header-Name') != env('TCPOS_API_SECRET')) {
            return response()->json(['message' => 'Access refused']);
        }

        return $this->getCustomerFunds($cardnum);
    }

    /**
     * Get verification field.
     */
    public function getCustomerVerificationField($cardnum): JsonResponse
    {
        return response()->json(['zipcode']);
    }

    /**
     * Verify user.
     */
    public function verifyCustomer(Request $request, $cardnum): JsonResponse
    {
        $verificationInput = $request->input('verificationFields');
        $value = data_get($verificationInput, '0.value');

        if ($value == data_get($this->getCustomer($cardnum), 'original.address.zipcode')) {
            return response()->json(true, 200);
        }

        return response()->json(['code' => '401', 'message' => 'Verification field value zipcode is incorrect'], 401);

    }
}
