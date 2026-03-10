<?php

namespace App\ThirdParty;

use App\Traits\GenerateClientRequestToken;
use App\Traits\HasResponse;

class BaseThirdParty
{
    /**
     * Create a new class instance.
     */
    use GenerateClientRequestToken, HasResponse;
}
