<?php

namespace App\Support\Services;

use Illuminate\Http\JsonResponse;
use App\Support\Services\BaseService;
use App\Http\Resources\PricingPlanResource;
use App\Support\Repositories\PricingPlanRepository;

class SubscriptionService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly PricingPlanRepository $pricingPlanRepository)
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
}
