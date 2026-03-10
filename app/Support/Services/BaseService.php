<?php

namespace App\Support\Services;

use App\Traits\HasResponse;
use App\Traits\GenerateToken;
use App\Traits\GenerateInvite;

class BaseService
{
    use HasResponse, GenerateToken, GenerateInvite;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
