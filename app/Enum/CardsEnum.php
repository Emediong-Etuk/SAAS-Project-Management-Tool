<?php

namespace App\Enum;

enum CardsEnum:string
{
    //
    case MasterCard='MasterCard';
    case Visa = 'Visa';
    case Verve = 'Verve';

    public static function values():array
    {
        return array_map(fn($card)=>$card->value, self::cases());
    } 
}
