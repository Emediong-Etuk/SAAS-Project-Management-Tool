<?php

namespace App\Support\Repositories;

use App\Models\TaskSubmission;
use Illuminate\Database\Eloquent\Collection;

class TaskSubmissionRepository
{
    public function create(array $data): TaskSubmission
    {
        return TaskSubmission::query()->create($data);
    }

    public function findByTaskId(string $task_id): Collection
    {
        return TaskSubmission::query()->where('task_id', $task_id)->get();
    }

    public function findByTaskIdAndUserId(string $task_id, string $user_id): Collection
    {
        return TaskSubmission::query()->where('task_id', $task_id)->where('user_id', $user_id)->get();
    }

    public function findBySubmittedTask(string $submission_id): ?TaskSubmission
    {
        return TaskSubmission::query()->where('id', $submission_id)->first();
    }
}
