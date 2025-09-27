<?php

namespace App\Support\Services;

use App\Traits\HasResponse;
use App\Traits\GenerateToken;

class BaseService
{
    use HasResponse, GenerateToken;
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
}
