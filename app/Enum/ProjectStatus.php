<?php

namespace App\Enum;

enum ProjectStatus:string
{
    //
    case PENDING='pending';
    case IN_PROGRESS='in_progress';
    case COMPLETED='completed';
    case ON_HOLD='on_hold';
    case CANCELLED='cancelled';

    public static function values():array
    {
        return array_column(self::cases(),'value');
    }
}
