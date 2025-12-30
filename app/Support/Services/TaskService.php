<?php

namespace App\Support\Services;

use App\Models\Task;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\TaskResource;
use App\Support\Services\BaseService;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Support\Repositories\NotificationRepository;
use App\Support\Repositories\TaskRepository;
use App\Support\Repositories\ProjectRepository;

class TaskService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly TaskRepository $taskRepository, private readonly ProjectRepository $projectRepository, private readonly NotificationRepository $notificationRepository)
    {
        //
    }

    public function getTasks(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        if ($request->user()->project_id !== $project->id) {
            return $this->badRequestResponse('You are not a member of this project');
        }

        $tasks = $this->taskRepository->getTasks($project->id);
        return $this->successResponse(data: [
            'tasks' => $tasks
        ]);
    }

    public function create(CreateTaskRequest $request, Tenant $tenant, Project $project): JsonResponse
    {
        if ($request->user()->tenant_id !== $tenant->id) {
            return $this->badRequestResponse('You are not a member of this tenant');
        }

        if ($request->user()->project_id !== $project->id) {
            return $this->badRequestResponse('You cannot create a task for this project');
        }


        $data = [
            'name' => $request->name,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'project_id' => $project->id,
            'tenant_id' => $tenant->id
        ];

        $task = $this->taskRepository->create($data)->refresh();
        $task->load('project');

        $this->notificationRepository->create([
            'user_id' => $request->user()->id,
            'alerts' => "Task created",
            'mark_read'=>true
        ]);

        $this->notifyAllMembers($project, $task,'has been created');

        return $this->successResponse(
            'Task created successfully',
            [
                'task' => new TaskResource($task)
            ]
        );
    }

    public function getSpecificTask(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        if ($request->user()->project_id !== $project->id) {
            return $this->badRequestResponse('You are not a member of this project');
        }

        if ($task->project_id !== $project->id) {
            return $this->badRequestResponse('You have not been assigned to this task');
        }

        $task = $this->taskRepository->find($task->id);
        $task->load('project');

        return $this->successResponse(data: [
            'task' => new TaskResource($task)
        ]);
    }

    public function update(UpdateTaskRequest $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        if ($request->user()->project_id !== $project->id) {
            return $this->badRequestResponse('You are not a member of this project');
        }

        $data = [
            'name' => $request->name ?? $task->name,
            'description' => $request->description ?? $task->description,
            'deadline' => $request->deadline ?? $task->deadline,
        ];

        $task = $this->taskRepository->find($task->id);

        $this->taskRepository->update($task->id, $data);

        $this->notificationRepository->create([
            'user_id'=>$request->user()->id,
            'alerts'=>"Task updated.",
        ]);

        $this->notifyAllMembers($project, $task,'has been updated');

        return $this->successResponse('Task updated successfully', [
            'task' => new TaskResource($task->refresh())
        ]);
    }

    public function delete(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        if ($request->user()->project_id !== $project->id) {
            return $this->badRequestResponse('You are not a member of this project');
        }

        
        $this->notificationRepository->create([
            'user_id'=>$request->user()->id,
            'alerts'=>"Task deleted",
            'mark_read'=>true
        ]);

        $this->notifyAllMembers($project, $task,'has been deleted');
        
        $this->taskRepository->delete($task->id);
        
        return $this->successResponse('Task deleted successfully');
    }

    public function markComplete(Request $request, Tenant $tenant, Project $project, Task $task): JsonResponse
    {
        if ($request->user()->project_id !== $project->id) {
            return $this->badRequestResponse('You are not a member of this project');
        }

        $data = [
            'completed' => true
        ];

        $this->taskRepository->update($task->id, $data);

        $this->notifyAllMembers($project,$task,'has been marked complete');

        return $this->successResponse('Task complete', [
            'task' => new TaskResource($task->refresh())
        ]);
    }


    public function notifyAllMembers(Project $project, Task $task,string $keyMessage){

        $projectMembers=$this->projectRepository->getProjectMembers($project->id);
        $task=$this->taskRepository->find($task->id);

        foreach($projectMembers as $member){
            $this->notificationRepository->create([
                    'user_id'=>$member->id,
                    'message'=>"{$task->name} for {$project->name} {$keyMessage}"
                ]);
        }
    }
}
