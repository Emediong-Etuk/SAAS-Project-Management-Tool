<?php

namespace App\Enum;

enum PlansEnum:string
{
    //
    case Free ='free';
    case Pro ='pro';
    case Enterprise ='enterprise';

    public static function values(): array
    {
        return array_map(fn($plan) => $plan->value, self::cases());
    }
}
