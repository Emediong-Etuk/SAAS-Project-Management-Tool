<?php

namespace App\Contracts\DataObjects;

class VerifyTransactionData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $token
    )
    {
        
    }

    public static function fromFlutterwave(array $data)
    {
        return new static(
            $data['status'],
            $data['data']['card']['token']
        );
    }
}
