<?php

namespace App\Enum;

enum FlutterwaveWebhookEvent:string
{
    //
    case CHARGE_COMPLETED = 'charge.completed';

    public static function value():string
    {
        return self::CHARGE_COMPLETED->value;

    }
}

