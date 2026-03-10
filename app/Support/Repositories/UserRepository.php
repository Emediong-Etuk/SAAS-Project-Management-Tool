<?php

namespace App\Support\Repositories;

use App\Models\User;
use App\Enum\UserRolesEnum;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function firstOrCreate(string $email, $data): User
    {
        return User::query()->firstOrCreate(['email' => $email], $data);
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }

    public function update(string $id, array $data): bool|int
    {
        return User::query()->where('id', $id)->update($data);
    }

    public function findByTenant(string $tenantId): ?User
    {
        return User::query()->where('tenant_id', $tenantId)->first();
    }

    public function findAllByTenant(string $tenantId): Collection
    {
        return User::query()->where('tenant_id', $tenantId)->get();
    }

    public function find(string $id): ?User
    {
        return User::query()->find($id);
    }

    public function getUsers(): Collection
    {
        return User::query()->where('expiry_date', now()->toDateString())->get();
    }

    public function delete(string $id): bool|null
    {
        return User::query()->where('id', $id)->delete();
    }

    public function findByRole(string $tenant_id, string $project_id): ?User
    {
        return User::query()->where('tenant_id', $tenant_id)->where('project_id', $project_id)->whereIn('role', [UserRolesEnum::PROJECT_MANAGER->value, UserRolesEnum::TENANT_ADMIN->value])->first();
    }

    public function findByName(string $name): ?User
    {
        return User::query()->where('name', $name)->first();
    }

    public function countAllUsers(): int
    {
        return User::query()->count();
    }

    public function searchUser(string $search): ?User
    {
        return User::query()->where('name', 'LIKE', '%' . $search . '%')
            ->orWhere('username', 'LIKE', '%' . $search . '%')
            ->first();
    }
}
