<?php

namespace App\Traits;

trait GenerateToken
{
    //
    public function generateToken($length = 4): int
    {
        $min = intval(pow(10, $length - 1));
        $max = intval(pow(10, $length) - 1);

        return rand($min, $max);
    }
}
