<?php

namespace App\ThirdParty;

use App\TransactionStatus;
use App\Traits\HasResponse;
use Flutterwave\Flutterwave;
use Illuminate\Http\Request;
use App\Traits\GenerateNonce;
use Flutterwave\Helper\Config;
use Flutterwave\Util\Currency;
use App\Enum\TransactionCategory;
use App\Traits\GenerateReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\CardPinRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\CreateCustomerRequest;
use App\Contracts\DataObjects\CreateCardData;
use App\Contracts\DataObjects\CreateCustomerData;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Support\Repositories\TransactionRepository;
use App\Contracts\Interface\SubscriptionPaymentInterface;

class SubscriptionPaymentApi implements SubscriptionPaymentInterface
{
    /**
     * Create a new class instance.
     */

    use GenerateNonce, GenerateReference, HasResponse;



    public function cardPayment(CardPaymentRequest $request): JsonResponse
    {

        Flutterwave::bootstrap();
        $reference = $this->generateReference(TransactionCategory::SUBSCRIPTION);
        $data = [
            'amount' => $request->amount,
            'currency' => Currency::NGN,
            'tx_ref' => $reference,
            'redirectUrl' => '',
            'additionalData' => [
                'payment_plan' => null,
                'card_details' => [
                    'card_number' => $request->card_number,
                    'cvv' => $request->cvv,
                    'expiry_month' => $request->expiry_month,
                    'expiry_year' => $request->expiry_year,
                ],
            ]
        ];

        $cardPayment = Flutterwave::create('card');
        $customerObj = $cardPayment->customer->create([
            'full_name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone_number,
        ]);

        $data['customer'] = $customerObj;

        $payload = $cardPayment->payload->create($data);
        $response = $cardPayment->initiate($payload);

        Cache::PUT('FLUTTERWAVE_CARD_PAYLOAD_' . $reference, $data, now()->addMinutes(10));

        return $this->successResponse(data: [
            'payment' => $response
        ]);
    }
    public function confirmCardPin(CardPinRequest $request): JsonResponse
    {
        Flutterwave::bootstrap();

        $reference = $request->query('tx_ref');
        $data = Cache::GET('FLUTTERWAVE_CARD_PAYLOAD_' . $reference);

        $data['additionalData']['authorization'] = [
            'mode' => 'pin',
            'pin' => $request->pin
        ];


        $cardPayment = Flutterwave::create('card');
        $payload = $cardPayment->payload->create($data);
        $response = $cardPayment->initiate($payload);

        $respArray = json_decode(json_encode($response), true);
        Log::info('Card Payment Response: ', ['response' => $respArray]);

        $transactionData = [
            'user_id' => $request->user()->id,
            'reference' => $reference,
            'amount' => $data['amount'],
            'status' => TransactionStatus::PENDING,
            'category' => TransactionCategory::SUBSCRIPTION,
            'amount' => $data['amount'],
            'transaction_id' => data_get($response, 'data_to_save.transactionId'),
        ];

        App(TransactionRepository::class)->create($transactionData);

        return $this->successResponse(message: "Kindly enter the OTP sent to 234701***5336. Didn't get the OTP? Dial *322*0# on your phone (MTN, Etisalat, Airtel) Glo, use *805*0#.",
        data: [
            'response' => $response
        ]);
    }

    public function validateCardPayment(ValidateCardPaymentRequest $request): JsonResponse
    {
        $reference = $request->query('tx_ref');

        $url = config('services.flutterwave.base_api_url') . '/validate-charge';

        $response = Http::withToken(config('services.flutterwave.secret_key'))
            ->post($url, [
                'otp' => $request->otp,
                'flw_ref' => $reference
            ]);

        return $this->successResponse(data: [
            'response' => $response
        ]);
    }

    public function verifyTransaction(string $reference):JsonResponse
    {
        $url=config('services.flutterwave.base_api_url') . "/transactions/$reference/verify";
        $auth=config('services.flutterwave.secret_key');

        $response=Http::withToken($auth)
        ->get($url);

        return $this->successResponse(data:[
            'transaction'=>$response->json()
        ]);
    }
}
