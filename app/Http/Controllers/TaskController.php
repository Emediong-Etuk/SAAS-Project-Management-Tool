<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\TaskService;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;

class TaskController extends Controller
{
    //

    public function __construct(private readonly TaskService $taskService){

    }

    public function getTasks(Request $request, Tenant $tenant, Project $project):JsonResponse
    {
        return $this->taskService->getTasks($request, $tenant, $project);
    }

    public function create(CreateTaskRequest $request,Tenant $tenant, Project $project):JsonResponse
    {
        return $this->taskService->create($request,$tenant, $project);
    }

    public function getSpecificTask(Request $request, Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->getSpecificTask($request, $tenant, $project, $task);
    }

    public function update(UpdateTaskRequest $request, Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->update($request,$tenant, $project, $task);
    }

    public function delete(Request $request, Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->delete($request, $tenant, $project, $task);
    }

    public function markComplete(Request $request, Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->markComplete($request,$tenant, $project, $task);
    }
}
