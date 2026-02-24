<?php

namespace App\Support\Services;


use App\Models\Tenant;
use App\Enum\PlansEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Resources\PricingPlanResource;
use App\Notifications\SubscriptionCancelled;
use App\Support\Repositories\UserRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SubscriptionSuccessful;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Support\Repositories\PricingPlanRepository;
use App\Contracts\Interface\SubscriptionPaymentInterface;

class SubscriptionService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly PricingPlanRepository $pricingPlanRepository, private readonly SubscriptionPaymentInterface $subscriptionPayment, private readonly UserRepository $userRepository)
    {
        //
    }

    public function displayPlans(Tenant $tenant): JsonResponse
    {
        $plans = $this->pricingPlanRepository->getAll();

        return $this->successResponse(message: 'Plans fetched successfully', data: [
            'plans' => PricingPlanResource::collection($plans)
        ]);
    }

    public function createPaymentPlan(Request $request):JsonResponse
    {
        $createPlan=$this->subscriptionPayment->createPaymentPlan();

        $this->userRepository->update($request->user()->id,[
            'payment_plan'=>$createPlan['data']['id']
        ]);

        return $this->successResponse(data:[
            'payment_plan'=>$createPlan
        ]);
    }

    public function getAuthModel(Tenant $tenant,CardPaymentRequest $request):JsonResponse
    {
        $authModel=$this->subscriptionPayment->getAuthModel($request);

        return $this->successResponse(data:[
            'response'=>$authModel
        ]);
    }

    public function cardPayment(Tenant $tenant, CardPaymentRequest $request): JsonResponse
    {

        $this->createPaymentPlan($request);
        $this->getAuthModel($tenant,$request);
        $payment = $this->subscriptionPayment->cardPayment($request);

        return $this->successResponse(message: "OTP has been sent to your phone number", data: [
            'payment' => $payment
        ]);
    }

    public function validateCardPayment(Tenant $tenant, ValidateCardPaymentRequest $request): JsonResponse
    {
        $validationResponse = $this->subscriptionPayment->validateCardPayment($request);

        if ($validationResponse->status === 'success') {
            $this->userRepository->update($request->user()->id, ['subscription_plan' => PlansEnum::Pro, 'expiry_date' => now()->addMonth(), 'reminder_date'=>now()->addMonth()->subDays(10)]);
            Notification::route('mail', $request->user()->email)->notify(new SubscriptionSuccessful($request->user()->name, 'Pro', now()->addMonth()->toFormattedDateString()));
        }

        return $this->successResponse(data: [
            'validationResponse' => $validationResponse,
            'user'=>new UserResource($request->user()->refresh())
        ]);
    }

    public function cancelSubscription(Tenant $tenant, Request $request): JsonResponse
    {
        $user=$request->user()->refresh();

        if ($user->subscription_plan === PlansEnum::Pro->value) {
            $cancelSubscription=$this->subscriptionPayment->cancelSubscription($request);
            if($cancelSubscription !== null){
                DB::transaction(function () use ($user) {
                    $this->userRepository->update($user->id, ['subscription_plan' => PlansEnum::Free, 'expiry_date' => null,'payment_plan'=>null]);
                });
    
                Notification::route('mail', $user->email)->notify(new SubscriptionCancelled($user->name, 'Pro', now()->addMonth()->toFormattedDateString()));
                return $this->successResponse(message:'Successfully Cancelled Subscription', data: ['user' => new UserResource($user)]);

            }
        }

        return $this->badRequestResponse(message:"Failed to cancel subscription");
    }
}
