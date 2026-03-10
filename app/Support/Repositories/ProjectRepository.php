<?php

namespace App\Support\Repositories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository
{
    public function getProjects(string $tenant_id): Collection
    {
        return Project::query()->where('tenant_id', $tenant_id)->get();
    }

    public function create(array $data): Project
    {
        return Project::create($data);
    }

    public function find(string $id): ?Project
    {
        return Project::query()->find($id);
    }

    public function update(string $id, array $data): bool|int
    {
        return Project::query()->where('id', $id)->update($data);
    }

    public function delete(string $id): ?bool
    {
        return Project::query()->where('id', $id)->delete();
    }

    public function getProjectMembersCount(string $project_id): int
    {
        return User::query()->where('project_id', $project_id)->count();
    }

    public function getProjectMembers(string $project_id): Collection
    {
        return User::query()->where('project_id', $project_id)->get();
    }

    public function findUsers(string $project_id, string $name): Collection
    {
        return User::query()->where('project_id', $project_id)->where('name', 'LIKE', '%'.$name.'%')->get();
    }

    public function countAllByTenant(string $tenant_id): int
    {
        return Project::query()->where('tenant_id', $tenant_id)->count();
    }

    public function findByTenant(string $tenant_id): ?Project
    {
        return Project::query()->where('tenant_id', $tenant_id)->first();
    }
}
