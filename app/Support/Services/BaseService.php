<?php

namespace App\Support\Services;

use App\Traits\GenerateInvite;
use App\Traits\GenerateToken;
use App\Traits\HasResponse;

class BaseService
{
    use GenerateInvite, GenerateToken, HasResponse;

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
