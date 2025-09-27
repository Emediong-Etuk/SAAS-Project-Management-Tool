<?php

namespace App\Support\Repositories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

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
}
