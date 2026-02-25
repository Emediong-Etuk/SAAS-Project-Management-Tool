<?php

namespace App\Contracts\DataObjects;

class ValidateCardChargeData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly string $email,
    ) {}

    public static function fromFlutterwave(array $data)
    {
        return new static(
            $data['status'],
            $data['message'],
            $data['data']['customer']['email']
        );
    }
}
