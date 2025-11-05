<?php

namespace App\Http\Controllers;

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

    public function displayPlans():JsonResponse
    {
        return $this->subscriptionService->displayPlans();
    }


    public function cardPayment(CardPaymentRequest $request):JsonResponse
    {
        return $this->subscriptionService->cardPayment($request);
    }

    public function validateCardPayment(ValidateCardPaymentRequest $request):JsonResponse
    {
        return $this->subscriptionService->validateCardPayment($request);
    }
}
