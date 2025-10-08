<?php

namespace App\Contract\Interface;

use App\Contracts\DataObjects\CreateCardData;
use App\Http\Requests\CreateCardRequest;


interface SubscriptionPaymentInterface
{
    //
    public function createCardPaymentMethod(CreateCardRequest $request):CreateCardData;
}
