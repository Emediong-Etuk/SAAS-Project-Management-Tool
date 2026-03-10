<?php

namespace App\Contracts\Interface;

use Illuminate\Http\Request;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Contracts\DataObjects\CreateCardChargeData;
use App\Contracts\DataObjects\ValidateCardChargeData;
use App\Contracts\DataObjects\VerifyTransactionData;
use Illuminate\Http\JsonResponse;

interface SubscriptionPaymentInterface
{
    public function createPaymentPlan():array;
    public function getAuthModel(CardPaymentRequest $request);
    public function cardPayment(CardPaymentRequest $request): CreateCardChargeData;
    public function validateCardPayment(ValidateCardPaymentRequest $request):ValidateCardChargeData;
    public function verifyTransaction($id):VerifyTransactionData;
    public function cancelSubscription(Request $request):JsonResponse;
}
