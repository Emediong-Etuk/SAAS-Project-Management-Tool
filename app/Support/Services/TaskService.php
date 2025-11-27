<?php

namespace App\Support\Services;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\TaskResource;
use App\Support\Services\BaseService;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\DeleteTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Support\Repositories\TaskRepository;
use App\Support\Repositories\ProjectRepository;

class TaskService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly TaskRepository $taskRepository, private readonly ProjectRepository $projectRepository)
    {
        //
    }

    public function getTasks(Tenant $tenant, Project $project):JsonResponse
    {
        $tasks=$this->taskRepository->getTasks($project->id);
        return $this->successResponse(data:[
            'tasks'=>$tasks
        ]);
    }

    public function create(CreateTaskRequest $request,Tenant $tenant, Project $project):JsonResponse
    {

        $data=[
            'name'=>$request->name,
            'description'=>$request->description,
            'deadline'=>$request->deadline,
            'project_id'=>$project->id,
            'tenant_id'=>$tenant->id
        ];

        $task=$this->taskRepository->create($data);
        $task->load('project');

        return $this->successResponse('Task created successfully',
        [
            'task'=>new TaskResource($task)
        ]);
    }

    public function getSpecificTask(Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        $task=$this->taskRepository->find($task->id);
        

        return $this->successResponse(data:[
            'task'=>new TaskResource($task)
        ]);
    }

    public function update(UpdateTaskRequest $request, Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        $data=[
            'name'=>$request->name ?? $task->name,
            'description'=>$request->description ?? $task->description,
            'deadline'=>$request->deadline ?? $task->deadline,
        ];

        $task=$this->taskRepository->find($task->id);
        
        $this->taskRepository->update($task->id, $data);

        return $this->successResponse('Task updated successfully',[
            'task'=>new TaskResource($task->refresh())
        ]);
    }

    public function delete(Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        $this->taskRepository->delete($task->id);
        
        return $this->successResponse('Task deleted successfully');
    }

    public function markComplete(Tenant $tenant, Project $project, Task $task):JsonResponse
    {
        $data=[
            'completed'=>true
        ];

        $this->taskRepository->update($task->id, $data);

        return $this->successResponse('Task complete',[
            'task'=>new TaskResource($task->refresh())
        ]);
    }
}
