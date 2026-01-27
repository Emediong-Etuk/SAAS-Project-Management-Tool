<?php

namespace App\Support\Services;

use ZipArchive;
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

    public function downloadSubmissionFile(Tenant $tenant, Project $project, Task $task, TaskSubmission $submittedTask, Request $request)
    {
        $submission = $this->taskSubmissionRepository->findBySubmittedTask($submittedTask->id);

        if (!$submission || empty($submission->submission_files)) {
            return response()->json(['error' => 'No files found'], 404);
        }

        $files = $submission->submission_files;

        if (count($files) === 1) {

            $relativePath = str_replace(env('APP_URL') . '/storage' . '/', '', $files[0]);
            $fullPath = Storage::disk('public')->path($relativePath);
            return response()->download($fullPath);
        }


        $zip = new ZipArchive();
        $zipPath = tempnam(sys_get_temp_dir(), 'submissions') . '.zip';
        $zip->open($zipPath, ZipArchive::CREATE);

        foreach ($files as $fileUrl) {
            Log::info('File URL: ' . $fileUrl);
            $relativePath = str_replace(env('APP_URL') . '/storage' . '/', '', $fileUrl);
            $fullPath = Storage::disk('public')->path($relativePath);

            if (file_exists($fullPath)) {
                $zip->addFile($fullPath, basename($fullPath));
            }
        }

        $zip->close();

        return response()->download($zipPath, 'submissions.zip')->deleteFileAfterSend(true);
    }
}
