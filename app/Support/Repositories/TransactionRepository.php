<?php

namespace App\Support\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    /**
     * Create a new class instance.
     */
    public function create(array $data):Transaction
    {
        return Transaction::query()->create($data);
    }

    public function findByRef(string $ref): ?Transaction
    {
        return Transaction::query()->where('reference', $ref)->first();
    }

    public function update(int $id, array $data):bool
    {
        return Transaction::query()->where('id', $id)->update($data);
    }
}
