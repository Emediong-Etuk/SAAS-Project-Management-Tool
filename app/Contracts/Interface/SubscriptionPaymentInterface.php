<?php

namespace App\Contracts\Interface;

use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\SubscriptionStatusData;
use App\Contracts\DataObjects\ValidateCardChargeData;
use App\Contracts\DataObjects\VerifyTransactionData;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface SubscriptionPaymentInterface
{

    public function cardPayment(CardPaymentRequest $request): CreateCardChargeData;

    public function validateCardPayment(ValidateCardPaymentRequest $request): ValidateCardChargeData;

    public function verifyTransaction($id): VerifyTransactionData;

    public function cancelSubscription(Request $request): JsonResponse;

    public function getSubscriptionStatus(Request $request): SubscriptionStatusData;
}
