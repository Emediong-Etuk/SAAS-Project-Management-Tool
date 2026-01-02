<?php

namespace App\Support\Services;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\TaskSubmission;
use Illuminate\Support\Facades\Log;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\TaskSubmissionRequest;
use App\Http\Resources\TaskSubmissionResource;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Support\Repositories\TaskSubmissionRepository;

class TaskSubmissionService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly TaskSubmissionRepository $taskSubmissionRepository)
    {
        //
    }

    public function submitTask(Tenant $tenant, Project $project, Task $task, TaskSubmissionRequest $request): JsonResponse
    {


        $data = [
            'submission_files',
            'comments' => $request->input('comment'),
            'user_id' => $request->user()->id,
            'task_id' => $task->id
        ];

        $path = [];

        foreach ($request->file('files') as $file) {
            $path[] = config('filesystems.disks.public.url') . '/' . $file->store('task_submissions', 'public');
        }

        $data['submission_files'] = $path;

        $submission = $this->taskSubmissionRepository->create($data);

        return $this->successResponse(data: [
            'submission' => new TaskSubmissionResource($submission)
        ]);
    }

    public function viewSubmissions(Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        $submissions = $this->taskSubmissionRepository->findByTaskId($task->id);

        return $this->successResponse(data: [
            'submissions' => TaskSubmissionResource::collection($submissions)
        ]);
    }

    public function viewUserSubmission(Tenant $tenant, Project $project, Task $task, Request $request): JsonResponse
    {
        $submissions = $this->taskSubmissionRepository->findByTaskIdAndUserId($task->id, $request->user()->id);

        return $this->successResponse(data: [
            'submissions' => TaskSubmissionResource::collection($submissions)
        ]);
    }

    public function downloadSubmissionFile(Tenant $tenant, Project $project, Task $task, TaskSubmission $submittedTask, Request $request): JsonResponse
    {

        $download = [];
        $extractedStoragePaths = [];
        $submission = $this->taskSubmissionRepository->findBySubmittedTask($submittedTask->id);

        foreach ($submission->submission_files as $sub) {

            $extractedStoragePaths[] = explode(env('APP_URL') . '/', $sub);
        }
        Log::info('extracted Storage Path', [$extractedStoragePaths]);

        foreach ($extractedStoragePaths as $path) {
            $download[] = response()->download($path[1]);
        }
        Log::info($download);
        return $this->successResponse(
            'Downloading..',
        );
    }
}
