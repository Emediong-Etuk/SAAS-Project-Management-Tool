<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\TaskSubmission;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\TaskService;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\SearchTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\TaskSubmissionRequest;
use App\Support\Services\TaskSubmissionService;

class TaskController extends Controller
{
    //

    public function __construct(private readonly TaskService $taskService, private readonly TaskSubmissionService $taskSubmissionService)
    {
        //

    }

    public function getTasks(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->taskService->getTasks($request, $tenant, $project);
    }

    public function create(CreateTaskRequest $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->taskService->create($request, $tenant, $project);
    }

    public function getSpecificTask(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        return $this->taskService->getSpecificTask($request, $tenant, $project, $task);
    }

    public function update(UpdateTaskRequest $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        return $this->taskService->update($request, $tenant, $project, $task);
    }

    public function delete(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        return $this->taskService->delete($request, $tenant, $project, $task);
    }

    public function markComplete(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        return $this->taskService->markComplete($request, $tenant, $project, $task);
    }

    public function submitTask(Tenant $tenant, Project $project, Task $task, TaskSubmissionRequest $request,): JsonResponse
    {
        return $this->taskSubmissionService->submitTask($tenant, $project, $task, $request);
    }

    public function viewSubmissions(Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        return $this->taskSubmissionService->viewSubmissions($tenant, $project, $task);
    }

    public function viewUserSubmissions(Tenant $tenant, Project $project, Task $task, Request $request): JsonResponse
    {
        return $this->taskSubmissionService->viewUserSubmission($tenant, $project, $task, $request);
    }

    public function downloadSubmissionFile(Tenant $tenant, Project $project, Task $task, TaskSubmission $submittedTask, Request $request): JsonResponse
    {
        return $this->taskSubmissionService->downloadSubmissionFile($tenant, $project, $task, $submittedTask, $request);
    }

    public function searchTask(SearchTaskRequest $request, Tenant $tenant, Project $project):JsonResponse
    {
        return $this->taskService->search($request,$tenant,$project);
    }
}
