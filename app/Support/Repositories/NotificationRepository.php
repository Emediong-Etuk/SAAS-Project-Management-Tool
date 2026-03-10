<?php

namespace App\Support\Repositories;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationRepository
{
    public function create(array $data): ?Notification
    {
        return Notification::query()->create($data);
    }

    public function getSelectedUnread(string $user_id, string $message): ?Notification
    {
        return Notification::query()->where('user_id', $user_id)->where('message', $message)->first();
    }

    public function findByMarkedRead(string $user_id): Collection
    {
        return Notification::query()->where('user_id', $user_id)->where('mark_read', true)->get();
    }

    public function getAll(string $user_id): Collection
    {
        return Notification::query()->where('user_id', $user_id)->get();
    }

    public function getUnread(string $user_id): Collection
    {
        return Notification::query()->where('user_id', $user_id)->where('mark_read', false)->get();
    }

    public function find(string $id): ?Notification
    {
        return Notification::query()->where('id', $id)->first();
    }

    public function delete(string $user_id, string $message): bool
    {
        return Notification::query()->where('user_id', $user_id)->where('message', $message)->delete();
    }
}
