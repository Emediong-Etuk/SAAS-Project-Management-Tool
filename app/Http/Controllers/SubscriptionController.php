<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardPaymentRequest;
use App\Support\Services\SubscriptionService;
use App\Http\Requests\ValidateCardPaymentRequest;

class SubscriptionController extends Controller
{
    //
    public function __construct(private readonly SubscriptionService $subscriptionService)
    {
        //
    }

    public function displayPlans(Tenant $tenant): JsonResponse
    {
        return $this->subscriptionService->displayPlans($tenant);
    }

    public function cardPayment(Tenant $tenant,CardPaymentRequest $request): JsonResponse
    {
        return $this->subscriptionService->cardPayment($tenant,$request);
    }

    public function validateCardPayment(Tenant $tenant,ValidateCardPaymentRequest $request): JsonResponse
    {
        return $this->subscriptionService->validateCardPayment($tenant,$request);
    }

    public function cancelSubscription(Tenant $tenant, User $user): JsonResponse
    {
        return $this->subscriptionService->cancelSubscription($tenant, $user);
    }
}
