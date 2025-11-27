<?php

namespace App\Contracts\DataObjects;

class CreateCardChargeData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $payment_type,
        public readonly string $flw_ref,
    ) {
        //
    }

    public static function fromFlutterWave(array $data): self
    {
        return new static(
            $data['status'],
            $data['data']['card']['type'],
            $data['data']['flw_ref']
        );
    }
}
