<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\CardPinRequest;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\CreateCustomerRequest;
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

    public function createCustomer(CreateCustomerRequest $request):JsonResponse
    {
        return $this->subscriptionService->createCustomer($request);
    }

    public function createCardMethod(CreateCardRequest $request):JsonResponse
    {
        return $this->subscriptionService->createCardMethod($request);
    }

    public function cardPayment(CardPaymentRequest $request):JsonResponse
    {
        return $this->subscriptionService->cardPayment($request);
    }

    public function confirmCardPin(CardPinRequest $request):JsonResponse
    {
        return $this->subscriptionService->confirmCardPin($request);
    }

    public function validateCardPayment(ValidateCardPaymentRequest $request):JsonResponse
    {
        return $this->subscriptionService->validateCardPayment($request);
    }
}
