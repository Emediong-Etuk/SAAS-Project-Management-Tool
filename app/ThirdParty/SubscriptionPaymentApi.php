<?php

namespace App\ThirdParty;

use App\Enum\PlansEnum;
use App\Traits\HasResponse;
use Illuminate\Http\Request;
use App\Traits\GenerateNonce;
use Flutterwave\Util\Currency;
use App\Enum\TransactionStatus;
use App\Enum\TransactionCategory;
use App\Traits\GenerateReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\SubscriptionStatusData;
use App\Support\Repositories\PricingPlanRepository;
use App\Support\Repositories\TransactionRepository;
use App\Contracts\DataObjects\VerifyTransactionData;
use App\Contracts\DataObjects\ValidateCardChargeData;
use App\Contracts\Interface\SubscriptionPaymentInterface;
use App\Enum\CardAuthModel;
use Illuminate\Support\Facades\Log;

class SubscriptionPaymentApi implements SubscriptionPaymentInterface
{
    /**
     * Create a new class instance.
     */

    use GenerateNonce, GenerateReference, HasResponse;


    public function createPaymentPlan(): array
    {
        $url = config('services.flutterwave.base_api_url') . '/payment-plans';
        $key = config('services.flutterwave.secret_key');
        $subscription_plan = App(PricingPlanRepository::class)->findByName(PlansEnum::Pro->value);

        $data = [
            'amount' => $subscription_plan->price,
            'name' => 'SAAS Project Management Tool Pro Subscription',
            'interval' => 'Monthly',
        ];

        $response = Http::withToken($key)->post($url, $data);

        return $response->json();
    }
    public function getAuthModel(CardPaymentRequest $request)
    {

        $encryptionKey = config('services.flutterwave.encryption_key');
        $key = config('services.flutterwave.secret_key');
        $url = config('services.flutterwave.base_api_url');
        $reference = $this->generateReference(TransactionCategory::SUBSCRIPTION);

        $subscription_plan = App(PricingPlanRepository::class)->findByName($request->query('subscription_plan'));

        $data = [
            'amount' => $subscription_plan->price,
            'currency' => Currency::NGN,
            'card_number' => $request->card_number,
            'cvv' => $request->cvv,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'tx_ref' => $reference,
            'email' => $request->user()->email,
            'payment_plan' => $request->user()->payment_plan,
        ];

        $jsonPayload = json_encode($data);

        $encrypt = openssl_encrypt(
            $jsonPayload,
            'DES-EDE3',
            $encryptionKey,
            OPENSSL_RAW_DATA
        );

        $encrypted_details = base64_encode($encrypt);

        $response = Http::withToken($key)->post($url . '/charges?type=card', [
            'client' => $encrypted_details,
        ]);

        Cache::put('Authorization_mode', $response->json()['meta']['authorization']['mode'], now()->addMinutes(20));

        return $response->json();
    }

    public function cardPayment(CardPaymentRequest $request): CreateCardChargeData
    {

        $encryptionKey = config('services.flutterwave.encryption_key');
        $key = config('services.flutterwave.secret_key');
        $url = config('services.flutterwave.base_api_url');
        $reference = $this->generateReference(TransactionCategory::SUBSCRIPTION);
        $mode = Cache::get('Authorization_mode');

        $subscription_plan = App(PricingPlanRepository::class)->findByName($request->query('subscription_plan'));

        $data = [
            'amount' => $subscription_plan->price,
            'currency' => Currency::NGN,
            'card_number' => $request->card_number,
            'cvv' => $request->cvv,
            'expiry_month' => $request->expiry_month,
            'expiry_year' => $request->expiry_year,
            'tx_ref' => $reference,
            'email' => $request->user()->email,
            'payment_plan' => $request->user()->payment_plan,
            'authorization' => [
                'mode' => $mode,
                'pin' => $mode === CardAuthModel::PIN->value ? $request->pin : null,
                'city' => $mode === CardAuthModel::AVS_NOAUTH->value ? $request->city : null,
                'address' => $mode === CardAuthModel::AVS_NOAUTH->value ? $request->address : null,
                'state' => $mode === CardAuthModel::AVS_NOAUTH->value ? $request->state : null,
                'country' => $mode === CardAuthModel::AVS_NOAUTH->value ? $request->country : null,
                'zipcode' => $mode === CardAuthModel::AVS_NOAUTH->value ? $request->zipcode : null,
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

        $response = Http::withToken($key)->post($url . '/charges?type=card', [
            'client' => $encrypted_details,
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
                'flw_ref' => $flw_ref,
                'subscription_plan' => $request->query('subscription_plan')
            ]
        ];

        App(TransactionRepository::class)->createOrUpdate($transactionData);

        return CreateCardChargeData::fromFlutterWave($response->json());
    }

    public function validateCardPayment(ValidateCardPaymentRequest $request): ValidateCardChargeData
    {
        $reference = $request->query('flw_ref');

        $url = config('services.flutterwave.base_api_url') . '/validate-charge';

        $response = Http::withToken(config('services.flutterwave.secret_key'))
            ->post($url, [
                'otp' => $request->otp,
                'flw_ref' => $reference
            ]);

        return ValidateCardChargeData::fromFlutterwave($response->json());
    }

    public function verifyTransaction($id): VerifyTransactionData
    {
        $url = config('services.flutterwave.base_api_url') . "/transactions/{$id}/verify";

        $response = Http::withToken(config('services.flutterwave.secret_key'))->get($url);

        return VerifyTransactionData::fromFlutterwave($response->json());
    }

    public function cancelSubscription(Request $request): JsonResponse
    {
        $paymentPlan = $request->user()->payment_plan;

        $url = config('services.flutterwave.base_api_url') . "/payment-plans/{$paymentPlan}/cancel";

        $key = config('services.flutterwave.secret_key');

        $response = Http::withToken($key)->put($url);

        return $this->successResponse(message: $response->json()['status'] . ',' .  $response->json()['message']);
    }

    public function getSubscriptionStatus(Request $request): SubscriptionStatusData
    {
        $paymentPlan = $request->user()->payment_plan;

        $url = config('services.flutterwave.base_api_url') . "/payment-plans/{$paymentPlan}";

        $key = config('services.flutterwave.secret_key');

        $response = Http::withToken($key)->get($url);

        Log::info('Subscription status response', ['response' => $response->json()]);

        return SubscriptionStatusData::fromFlutterwave($response->json());
    }
}
