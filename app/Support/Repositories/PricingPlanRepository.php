<?php

namespace App\Support\Repositories;

use App\Models\PricingPlan;
use Illuminate\Database\Eloquent\Collection;


class PricingPlanRepository
{
    public function getAll(): Collection {
        return PricingPlan::query()->get();
    }
}
