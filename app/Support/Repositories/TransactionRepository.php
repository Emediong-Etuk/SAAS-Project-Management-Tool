<?php

namespace App\Support\Repositories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Collection;

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

    public function findByUser(string $userId): ?Transaction
    {
        return Transaction::query()->where('user_id', $userId)->first();
    }

    public function getAll(): Collection
    {
        return Transaction::query()->get();
    }
}
