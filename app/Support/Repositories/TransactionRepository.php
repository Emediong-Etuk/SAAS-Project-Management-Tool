<?php

namespace App\Support\Repositories;

use App\Models\Transaction;

class TransactionRepository
{
    /**
     * Create a new class instance.
     */
    public function createOrUpdate(array $data): Transaction
    {
        return Transaction::query()->createOrFirst($data);
    }

    public function findByRef(string $ref): ?Transaction
    {
        return Transaction::query()->where('reference', $ref)->first();
    }

    public function update(string $id, array $data): bool
    {
        return Transaction::query()->where('id', $id)->update($data);
    }
}
