<?php

namespace App\Http\Controllers;

use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Models\Tenant;
use App\Support\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function __construct(private readonly SubscriptionService $subscriptionService) {}

    public function displayPlans(Tenant $tenant): JsonResponse
    {
        return $this->subscriptionService->displayPlans($tenant);
    }

    public function cardPayment(Tenant $tenant, CardPaymentRequest $request): JsonResponse
    {
        return $this->subscriptionService->cardPayment($tenant, $request);
    }

    public function validateCardPayment(Tenant $tenant, ValidateCardPaymentRequest $request): JsonResponse
    {
        return $this->subscriptionService->validateCardPayment($tenant, $request);
    }

    public function cancelSubscription(Tenant $tenant, Request $request): JsonResponse
    {
        return $this->subscriptionService->cancelSubscription($tenant, $request);
    }

    public function getSubscriptionStatus(Tenant $tenant, Request $request): JsonResponse
    {
        return $this->subscriptionService->getSubscriptionStatus($tenant, $request);
    }
}
