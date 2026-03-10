<?php

namespace App\Contracts\Interface;

use App\Http\Requests\CardPaymentRequest;
use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\VerifyCardChargeData;
use App\Http\Requests\ValidateCardPaymentRequest;


interface SubscriptionPaymentInterface
{
    public function cardPayment(CardPaymentRequest $request): CreateCardChargeData;
    public function validateCardPayment(ValidateCardPaymentRequest $request): VerifyCardChargeData;
    public function verifyTransaction($id);
}
