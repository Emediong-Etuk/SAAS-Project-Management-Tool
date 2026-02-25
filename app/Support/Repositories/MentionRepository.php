<?php

namespace App\Support\Repositories;

use App\Models\Mention;
use Illuminate\Support\Collection;

class MentionRepository
{
    public function create(array $data): Mention
    {
        return Mention::query()->create($data);
    }

    public function getMentions(string $comment_id): Collection
    {
        return Mention::query()->where('comment_id', $comment_id)->get();
    }
}
