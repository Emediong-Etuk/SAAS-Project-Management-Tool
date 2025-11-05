<?php

namespace App\ThirdParty;

use App\TransactionStatus;
use App\Traits\HasResponse;
use App\Traits\GenerateNonce;
use Flutterwave\Helper\Config;
use Flutterwave\Util\Currency;
use App\Enum\TransactionCategory;
use App\Traits\GenerateReference;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\VerifyCardChargeData;
use App\Support\Repositories\TransactionRepository;
use App\Contracts\Interface\SubscriptionPaymentInterface;

class SubscriptionPaymentApi implements SubscriptionPaymentInterface
{
    /**
     * Create a new class instance.
     */

    use GenerateNonce, GenerateReference, HasResponse;



    public function cardPayment(CardPaymentRequest $request): CreateCardChargeData
    {

        $encryptionKey = config('services.flutterwave.encryption_key');
        $key = config('services.flutterwave.secret_key');
        $url = config('services.flutterwave.base_api_url');
        $reference = $this->generateReference(TransactionCategory::SUBSCRIPTION);

        $data = [
            'amount' => $request->amount,
            'currency' => Currency::NGN,
            'card_number' => $request->card_number,
            'cvv' => $request->cvv,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'tx_ref' => $reference,
            'email' => $request->user()->email,
            'authorization' => [
                'mode' => 'pin',
                'pin' => $request->pin
            ]
        ];

        $jsonPayload = json_encode($data);

        $encrypt = openssl_encrypt(
            $jsonPayload,
            'DES-EDE3',
            $encryptionKey,
            OPENSSL_RAW_DATA
        );

        $encrypted_details = base64_encode($encrypt);

        $response = Http::withToken($key)
            ->post($url . '/charges?type=card', [
                'client' => $encrypted_details
            ]);

        $flw_ref = $response->json()['data']['flw_ref'];

        $transactionData = [
            'user_id' => $request->user()->id,
            'reference' => $reference,
            'amount' => $data['amount'],
            'status' => TransactionStatus::PENDING,
            'category' => TransactionCategory::SUBSCRIPTION,
            'transaction_id' => $response->json()['data']['id'],
            'meta' => [
                'flw_ref' => $flw_ref
            ]
        ];

        App(TransactionRepository::class)->createOrUpdate($transactionData);

        return CreateCardChargeData::fromFlutterWave($response->json());
    }

    public function validateCardPayment(ValidateCardPaymentRequest $request): VerifyCardChargeData
    {
        $reference = $request->query('flw_ref');

        $url = config('services.flutterwave.base_api_url') . '/validate-charge';

        $response = Http::withToken(config('services.flutterwave.secret_key'))
            ->post($url, [
                'otp' => $request->otp,
                'flw_ref' => $reference
            ]);

        return VerifyCardChargeData::fromFlutterwave($response->json());
    }

    public function verifyTransaction(string $reference): string
    {
        $url = config('services.flutterwave.base_api_url') . "/transactions/$reference/verify";
        $auth = config('services.flutterwave.secret_key');

        $response = Http::withToken($auth)
            ->get($url);

        return $response->json()['data']['status'];
    }
}
