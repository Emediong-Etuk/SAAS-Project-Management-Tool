<?php

namespace App\Support\Services;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\CardPinRequest;
use App\Support\Services\BaseService;
use App\Http\Requests\CreateCardRequest;
use App\Http\Requests\CardPaymentRequest;
use App\Http\Resources\PricingPlanResource;
use App\Http\Requests\CreateCustomerRequest;
use App\Http\Requests\ValidateCardPaymentRequest;
use App\Support\Repositories\PricingPlanRepository;
use App\Contracts\Interface\SubscriptionPaymentInterface;


class SubscriptionService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly PricingPlanRepository $pricingPlanRepository, private readonly SubscriptionPaymentInterface $subscriptionPayment)
    {
        //
    }

    public function displayPlans():JsonResponse
    {
        $plans=$this->pricingPlanRepository->getAll();

        return $this->successResponse(message:'Plans fetched successfully',data:[
            'plans'=>PricingPlanResource::collection($plans)
        ]);
    }

    public function createCustomer(CreateCustomerRequest $request):JsonResponse
    {
        $customer=$this->subscriptionPayment->createCustomer($request);

        return $this->successResponse(data:[
            'customer'=>$customer
        ]);
    }

    public function createCardMethod(CreateCardRequest $request):JsonResponse
    {
        $cardMethod=$this->subscriptionPayment->createCardPaymentMethod($request);

        return $this->successResponse(data:[
            'cardMethod'=>$cardMethod
        ]);
    }

    public function cardPayment(CardPaymentRequest $request):JsonResponse
    {
        $payment=$this->subscriptionPayment->cardPayment($request);

        return $this->successResponse(data:[
            'payment'=>$payment
        ]);
    }

    public function confirmCardPin(CardPinRequest $request):JsonResponse
    {
        $paymentConfirmation=$this->subscriptionPayment->confirmCardPin($request);

        return $this->successResponse(data:[
            'transactionDetails'=>$paymentConfirmation
        ]);
    }

    public function validateCardPayment(ValidateCardPaymentRequest $request):JsonResponse
    {
        $validationResponse=$this->subscriptionPayment->validateCardPayment($request);

        return $this->successResponse(data:[
            'validationResponse'=>$validationResponse
        ]);
    }
}
