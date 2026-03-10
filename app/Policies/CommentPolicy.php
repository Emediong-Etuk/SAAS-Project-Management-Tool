<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CommentPolicy
{
    public function delete(User $user, Comment $comment): Response
    {
        if ($user->id !== $comment->user_id) {
            return Response::deny();
        }

        return Response::allow();
    }
}
