<?php

namespace App\Enum;

enum PlansEnum:string
{
    case Free ='Free';
    case Pro ='Pro';

    public static function values(): array
    {
        return array_map(fn($plan) => $plan->value, self::cases());
    }
}
