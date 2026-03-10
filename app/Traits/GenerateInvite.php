<?php

namespace App\Traits;

trait GenerateInvite
{
    //

    public function generateInviteLink(string $code): string
    {
        return config('app.url').'/api/auth/signup?code='.$code;
    }

    public function generateInviteCode($length = 40): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }
}
