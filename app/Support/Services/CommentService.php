<?php

namespace App\Support\Services;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Comment;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Services\BaseService;
use App\Http\Resources\CommentResource;
use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Support\Repositories\CommentRepository;

class CommentService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly CommentRepository $commentRepository)
    {
        //
    }

    public function getComments(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {

        $comment = $this->commentRepository->getAllCommentsForTask($task->id);
        if ($project->id !== $request->user()->project_id) {
            return $this->badRequestResponse('Not authorized');
        }

        return $this->successResponse(data: [
            'comments' => $comment
        ]);
    }

    public function getSpecificComment(Tenant $tenant, Project $project, Task $task, Comment $comment): JsonResponse
    {

        $comment = $this->commentRepository->find($comment->id);

        return $this->successResponse(data: [
            'comment' => new CommentResource($comment)
        ]);
    }

    public function create(Tenant $tenant, Project $project, Task $task, CreateCommentRequest $request): JsonResponse
    {

        $data = [
            'comment' => $request->comment,
            'project_id' => $project->id,
            'tenant_id' => $tenant->id,
            'task_id' => $task->id,
            'user_id' => $request->user()->id
        ];
        $comment = $this->commentRepository->create($data);

        

        return $this->successResponse('Comment added successfully', [
            'comment' => new CommentResource($comment)
        ]);
    }

    public function update(Tenant $tenant, Project $project, Task $task, Comment $comment, UpdateCommentRequest $request): JsonResponse
    {
        $data = [
            'comment' => $request->comment,
        ];

        $this->commentRepository->update($comment->id, $data);

        return $this->successResponse('Comment updated successfully', [
            'comment' => new CommentResource($this->commentRepository->find($comment->id))
        ]);
    }

    public function delete(Tenant $tenant, Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->commentRepository->delete($comment->id);
        return $this->successResponse('Comment deleted successfully');
    }
}
