<?php

namespace App\Contracts\DataObjects;

class CreateCustomerData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        public string $id
    )
    {
        //
    }

    public static function fromFlutterWave(array $data): self
    {
        return new self(
            $data['id'],
        );
    }
}
