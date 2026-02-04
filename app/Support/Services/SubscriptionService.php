<?php

namespace App\Support\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Enum\PlansEnum;
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

    public function cardPayment(Tenant $tenant, CardPaymentRequest $request): JsonResponse
    {

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
            'validationResponse' => $validationResponse
        ]);
    }

    public function cancelSubscription(Tenant $tenant, User $user): JsonResponse
    {

        if ($user->subscription_plan === 'pro') {

            DB::transaction(function () use ($user) {
                $this->userRepository->update($user->id, ['subscription_plan' => PlansEnum::Free, 'expiry_date' => null]);
            });

            Notification::route('mail', $user->email)->notify(new SubscriptionCancelled($user->name, 'Pro', now()->addMonth()->toFormattedDateString()));
            return $this->successResponse(message: 'Subscription cancelled successfully', data: ['user' => new UserResource($user)]);
        }

        return $this->badRequestResponse(message: 'User does not have an active premium subscription to cancel');
    }
}
