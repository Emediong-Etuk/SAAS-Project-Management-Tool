<?php

namespace App\Support\Services;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Models\Tenant;
use App\Support\Repositories\CommentRepository;
use App\Support\Repositories\MentionRepository;
use App\Support\Repositories\NotificationRepository;
use App\Support\Repositories\ProjectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CommentService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly MentionRepository $mentionRepository,
        private readonly NotificationRepository $notificationRepository
    ) {
        //
    }

    public function getComments(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {

        $comment = $this->commentRepository->getAllCommentsForTask($task->id);
        if ($project->id !== $request->user()->project_id) {
            return $this->badRequestResponse('Not authorized');
        }

        return $this->successResponse(data: [
            'comments' => $comment,
        ]);
    }

    public function getSpecificComment(Tenant $tenant, Project $project, Task $task, Comment $comment): JsonResponse
    {

        $comment = $this->commentRepository->find($comment->id);

        return $this->successResponse(data: [
            'comment' => new CommentResource($comment),
        ]);
    }

    public function create(Tenant $tenant, Project $project, Task $task, CreateCommentRequest $request): JsonResponse
    {
        $authUser = $request->user()->username;
        $projectMembers = $this->getAllProjectMembers($project);

        $names = [];

        if (Str::contains($request->comment, '@')) {
            foreach ($projectMembers as $projectMember) {
                $names[] = $projectMember->name;
            }
        }

        $data = [
            'comment' => $request->comment,
            'project_id' => $project->id,
            'tenant_id' => $tenant->id,
            'task_id' => $task->id,
            'user_id' => $request->user()->id,
        ];

        $comment = $this->commentRepository->create($data);

        foreach ($projectMembers as $projectMember) {
            $this->mentionRepository->create([
                'user_id' => $projectMember->id,
                'username' => $projectMember->username,
                'comment_id' => $comment->id,
                'account_link' => config('app.url')."/api/{$tenant->id}/account/{$projectMember->username}",
            ]);

            $this->notificationRepository->create([
                'message' => "$authUser mentioned you",
                'user_id' => $projectMember->id,
            ]);
        }

        $mentions = $this->mentionRepository->getMentions($comment->id);

        return $this->successResponse('Comment added successfully', [
            'mentions' => $mentions,
            'names' => $names,
            'comment' => new CommentResource($comment),
        ]);
    }

    public function update(Tenant $tenant, Project $project, Task $task, Comment $comment, UpdateCommentRequest $request): JsonResponse
    {
        $data = [
            'comment' => $request->comment,
        ];

        $this->commentRepository->update($comment->id, $data);

        return $this->successResponse('Comment updated successfully', [
            'comment' => new CommentResource($this->commentRepository->find($comment->id)),
        ]);
    }

    public function delete(Tenant $tenant, Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->commentRepository->delete($comment->id);

        return $this->successResponse('Comment deleted successfully');
    }

    public function getAllProjectMembers(Project $project)
    {
        return $this->projectRepository->getProjectMembers($project->id);
    }
}
