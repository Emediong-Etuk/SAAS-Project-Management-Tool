<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\CommentService;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;

class CommentController extends Controller
{
    //
    public function __construct(private readonly CommentService $commentService)
    {

    }

    public function getComments(Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->commentService->getComments($tenant, $project, $task);
    }

    public function getSpecificComment(Tenant $tenant, Project $project, Task $task, Comment $comment):JsonResponse
    {
        return $this->commentService->getSpecificComment($tenant, $project, $task, $comment);
    }

    public function create(Tenant $tenant , Project $project, Task $task, CreateCommentRequest $request):JsonResponse
    {
        return $this->commentService->create($tenant, $project, $task, $request);
    }

    public function update(Tenant $tenant, Project $project, Task $task, Comment $comment, UpdateCommentRequest $request):JsonResponse
    {
        return $this->commentService->update($tenant, $project, $task,$comment, $request);
    }

    public function delete(Tenant $tenant, Project $project, Task $task, Comment $comment):JsonResponse
    {
        return $this->commentService->delete($tenant, $project, $task,$comment);
    }
}
