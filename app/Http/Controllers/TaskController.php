<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\TaskService;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Tenant;
use App\Models\Task;

class TaskController extends Controller
{
    //

    public function __construct(private readonly TaskService $taskService){

    }

    public function getTasks(Tenant $tenant, Project $project):JsonResponse
    {
        return $this->taskService->getTasks($tenant, $project);
    }

    public function create(CreateTaskRequest $request,Tenant $tenant, Project $project):JsonResponse
    {
        return $this->taskService->create($request,$tenant, $project);
    }

    public function getSpecificTask(Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->getSpecificTask($tenant, $project, $task);
    }

    public function update(UpdateTaskRequest $request, Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->update($request,$tenant, $project, $task);
    }

    public function delete(Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        return $this->taskService->delete($tenant, $project, $task);
    }
}
