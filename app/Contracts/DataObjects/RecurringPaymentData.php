<?php

namespace App\Contracts\DataObjects;

class RecurringPaymentData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $tx_ref
    )
    {
        
    }

    public static function fromFlutterwave(array $data)
    {
        return new static(
            $data['status'],
            $data['data']['tx_ref']
        );
    }
}
