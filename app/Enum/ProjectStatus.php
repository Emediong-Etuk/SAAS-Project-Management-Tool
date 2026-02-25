<?php

namespace App\Enum;

enum ProjectStatus: string
{
    //
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case OVERDUE = 'overdue';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
