<?php

namespace App;

enum SubscriptionStatus: string
{
    //

    case Active = 'active';

    public function value(): string
    {
        return $this->value;
    }
}
