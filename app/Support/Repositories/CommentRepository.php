<?php

namespace App\Support\Repositories;

use App\Models\Comment;
use Illuminate\Support\Collection;

class CommentRepository
{
    public function getAllCommentsForTask($taskId): Collection
    {
        return Comment::where('task_id', '=', $taskId, 'and')->get();
    }

    public function create(array $data): Comment
    {
        return Comment::query()->create($data);
    }

    public function find(string $id): ?Comment
    {
        return Comment::query()->find($id);
    }

    public function update(string $id, array $data): bool|int
    {
        return Comment::query()->where('id', $id)->update($data);
    }

    public function delete(string $id): bool|int
    {
        return Comment::query()->where('id', $id)->delete();
    }
}
