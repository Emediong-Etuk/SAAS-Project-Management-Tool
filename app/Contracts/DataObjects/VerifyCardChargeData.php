<?php

namespace App\Contracts\DataObjects;

class VerifyCardChargeData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        // public readonly string $amount
    ) {
        //
    }
    public static function fromFlutterwave(array $data)
    {
        return new static(
            $data['status'],
            $data['message'],
            // $data['data']['amount']
        );
    }
}
