<?php

namespace App\Enum;

enum PlansEnum:string
{
    //
    case Free ='free';
    case Pro ='pro';

    public static function values(): array
    {
        return array_map(fn($plan) => $plan->value, self::cases());
    }
}
