<?php

namespace App\Contracts\DataObjects;

class CreateCardChargeData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly string $instruction,
        public readonly string $reference,
        public readonly string $flw_ref
    ) {
        //
    }

    public static function fromFlutterWave(array $data): self
    {
        return new static(
            $data['status'],
            $data['message'],
            $data['data']['processor_response'],
            $data['data']['tx_ref'],
            $data['data']['flw_ref'],
        );
    }
}
