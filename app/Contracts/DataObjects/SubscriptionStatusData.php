<?php

namespace App\Contracts\DataObjects;

class SubscriptionStatusData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $status
    ) {
        //
    }

    public static function fromFlutterwave(array $data): self
    {
        return new static(
            $data['data']['status']
        );
    }
}
