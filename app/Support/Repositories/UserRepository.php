<?php

namespace App\Support\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function create(array $data):User
    {
        return User::query()->create($data);

    }

    public function findByEmail(string $email):?User
    {
        return User::query()->where('email',$email)->first();
    }

    public function update(string $id, array $data):bool|int
    {
        return User::query()->where('id',$id)->update($data);
    }

    public function getAllByTenantId(string $tenantId):Collection
    {
        return User::query()->where('tenant_id',$tenantId)->get();
    }

    public function findByTenant(string $tenantId):?User
    {
        return User::query()->where('tenant_id',$tenantId)->first();
    }

    public function findAllByTenant(string $tenantId):Collection
    {
        return User::query()->where('tenant_id',$tenantId)->get();
    }

    public function find(string $id):?User
    {
        return User::query()->find($id);
    }

    public function getUsers():Collection
    {
        return User::query()->where('expiry_date',now()->toDateString())->get();
    }
}
