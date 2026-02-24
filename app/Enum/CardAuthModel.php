<?php

namespace App\Enum;

enum CardAuthModel:string
{
    //
    case PIN='pin';
    case AVS_NOAUTH='avs_noauth';

    public static function values():array
    {
        return array_column(self::cases(),'value');
    }
}
