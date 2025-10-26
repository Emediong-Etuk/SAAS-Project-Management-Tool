<?php

namespace App\Contracts\Interface;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\CardPinRequest;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\CreateCustomerRequest;
use App\Contracts\DataObjects\CreateCardData;
use App\Contracts\DataObjects\CreateCustomerData;
use App\Http\Requests\ValidateCardPaymentRequest;


interface SubscriptionPaymentInterface
{
    //
    public function cardPayment(CardPaymentRequest $request):JsonResponse;
    public function confirmCardPin(CardPinRequest $request):JsonResponse;
    public function validateCardPayment(ValidateCardPaymentRequest $request):JsonResponse;
    public function verifyTransaction(string $reference):JsonResponse;
}
