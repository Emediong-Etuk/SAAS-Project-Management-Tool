<?php

namespace App\Contracts\Interface;

use App\Http\Requests\CardPaymentRequest;
use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\VerifyCardChargeData;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use Illuminate\Http\JsonResponse;


interface SubscriptionPaymentInterface
{
    //
    public function cardPayment(CardPaymentRequest $request): CreateCardChargeData;
    public function validateCardPayment(ValidateCardPaymentRequest $request): VerifyCardChargeData;
}
