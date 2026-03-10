<?php

namespace App\Support\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository
{
    public function getTasks(string $project_id): Collection
    {
        return Task::query()->where('project_id', $project_id)->get();
    }

    public function create(array $data): Task
    {
        return Task::query()->create($data);
    }

    public function find(string $id): ?Task
    {
        return Task::query()->find($id);
    }

    public function update(string $id, array $data): int|bool
    {
        return Task::query()->where('id', $id)->update($data);
    }

    public function delete(string $id): ?bool
    {
        return Task::query()->where('id', $id)->delete();
    }

    public function search(string $project_id, string $query): Collection
    {
        if (empty($query)) {
            return Task::query()->where('project_id', $project_id)->get();
        }

        $terms = array_filter(explode(' ', trim($query)));

        return Task::query()->where('project_id', $project_id)
            ->where(function ($q) use ($terms) {
                foreach ($terms as $term) {
                    $q->where('name', 'LIKE', '%'.$term.'%')
                        ->orWhere('description', 'LIKE', '%'.$term.'%');
                }
            })->get();
    }

    public function countAllByTenant(string $tenant_id): int
    {
        return Task::query()->where('tenant_id', $tenant_id)->count();
    }

    public function completedTasks(string $tenant_id): int
    {
        return Task::query()->where('tenant_id', $tenant_id)->where('completed', true)->count();
    }

    public function pendingTasks(string $tenant_id): int
    {
        return Task::query()->where('tenant_id', $tenant_id)->where('completed', false)->count();
    }

    public function countAllForProject(string $project_id): int
    {
        return Task::query()->where('project_id', $project_id)->count();
    }

    public function getCompletedTasksForProject(string $project_id): int
    {
        return Task::query()->where('project_id', $project_id)->where('completed',true)->count();
    }
}
