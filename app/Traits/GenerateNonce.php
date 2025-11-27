<?php

namespace App\Traits;


trait GenerateNonce
{
    //

    public function generateNonce():string
    {
        return bin2hex(random_bytes(6));
    }
}
