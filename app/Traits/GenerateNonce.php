<?php

namespace App\Traits;

trait GenerateNonce
{
    //

    public function generateNonce(int $length=16):string
    {
        $characters='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $nonce='';
        for ($i=0; $i < $length; $i++) {
            $nonce .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $nonce;
    }
}
