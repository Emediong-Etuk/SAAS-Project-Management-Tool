<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GenerateMeetingId
{
    //
    public function generateMeetingId(): string
    {
        return Str::uuid()->toString();
    }
}
