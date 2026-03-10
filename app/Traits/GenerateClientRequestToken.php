<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait GenerateClientRequestToken
{
    //
    public function generateClientRequestToken(): string
    {
        return Str::uuid()->toString();
    }
}
