<?php

namespace App\Support\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class ProjectRepository
{
    public function getProjects(string $tenant_id):Collection
    {
        return Project::query()->where('tenant_id', $tenant_id)->get();
    }

    public function create(array $data):Project
    {
        return Project::create($data);
    }

    public function find(string $id):?Project
    {
        return Project::query()->find($id);
    }

    public function update(string $id, array $data):bool|int
    {
        return Project::query()->where('id', $id)->update($data);
    }

    public function delete(string $id):bool|null
    {
        return Project::query()->where('id', $id)->delete();
    }

    public function getProjectMembersCount(string $project_id):int
    {
        return User::query()->where('project_id', $project_id)->count();
    }

    public function getProjectMembers(string $project_id):Collection
    {
        return User::query()->where('project_id', $project_id)->get();
    }
}
