<?php

namespace App\Contracts\DataObjects;

class CreateCardData
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        // private readonly string $status,
        // private readonly array $card,
        // private readonly int $id,
    )
    {
        //
    }

    public static function fromFlutterWave(array $data):self
    {
        return new static (
            $data,
            // $data['status'],
            // $data['data']['card'],
            // $data['data']['id']
        );
    }
}
