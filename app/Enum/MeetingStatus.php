<?php

namespace App\Enum;

enum MeetingStatus: string
{
    //
    case TRUE = 'true';
    case FALSE = 'false';

    public static function value(bool $value): MeetingStatus
    {
        return $value ? MeetingStatus::TRUE : MeetingStatus::FALSE;
    }
}
