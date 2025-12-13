<?php

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Auth\Access\Response;


class CommentPolicy
{



    public function getSpecific(User $user, Comment $comment): Response
    {
        return $this->validate($user, $comment);
    }


    public function create(User $user, Task $task): Response
    {
        if ($user->project_id !== $task->project_id) {
            return Response::denyAsNotFound("Not authorized");
        };

        return Response::allow();
    }


    public function update(User $user, Comment $comment): Response
    {
        return $this->validate($user, $comment);
    }


    public function delete(User $user, Comment $comment): Response
    {
        return $this->validate($user, $comment);
    }


    public function validate(User $user, Comment $comment): Response
    {

        if ($user->project_id !== $comment->project_id) {
            return Response::denyAsNotFound('Not authorized');
        }

        return Response::allow();
    }
}
