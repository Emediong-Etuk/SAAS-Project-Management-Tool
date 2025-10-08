<?php

namespace App\ThirdParty;

use App\Traits\GenerateNonce;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\CreateCardRequest;
use App\Contracts\DataObjects\CreateCardData;

class SubscriptionPaymentApi
{
    /**
     * Create a new class instance.
     */

    use GenerateNonce;

    public function __construct()
    {
        //
    }

    public function createCardPaymentMethod(CreateCardRequest $request):CreateCardData
    {
        $nonce = $this->generateNonce();
        $url = config('services.flutterwave.base_api_url') . '/payment-methods';
        $auth = config('services.flutterwave.key');

        $request = Http::withToken($auth)->post($url, [
            'type' => 'card',
            'card' => [
                'encrypted_card_number' => bcrypt($request->card_number),
                'encrypted_expiry_month' => bcrypt($request->expiry_month),
                'encrypted_expiry_year' => bcrypt($request->expiry_year),
                'encrypted_cvv' => bcrypt($request->cvv),
                'nonce' => $nonce,
                'cof' => [
                    'enabled' => 'true'
                ]
            ]
        ]);

        return CreateCardData::fromFlutterWave($request->json());
    }
}
