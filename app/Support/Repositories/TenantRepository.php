<?php

namespace App\Support\Repositories;

use App\Models\Tenant;

class TenantRepository
{
    public function create(array $data): Tenant
    {
        return Tenant::create($data);
    }

    public function find(string $id): ?Tenant
    {
        return Tenant::query()->find($id);
    }

    public function update(string $id, array $data): ?bool
    {
        return Tenant::query()->where('id', $id)->update($data);
    }

    public function delete(string $id): ?bool
    {
        return Tenant::query()->where('id', $id)->delete();
    }

    public function countAllTenants(): int
    {
        return Tenant::query()->count('*');
    }
}
