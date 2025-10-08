<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Support\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

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
}
