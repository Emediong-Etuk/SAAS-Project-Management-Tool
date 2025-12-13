<?php

namespace App\Support\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Project;
use App\Enum\UserRolesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Services\BaseService;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Support\Repositories\UserRepository;
use App\Support\Repositories\ProjectRepository;

class ProjectService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly ProjectRepository $projectRepository, private readonly UserRepository $userRepository)
    {
        //
    }

    public function getProjects(Request $request, Tenant $tenant): JsonResponse
    {
        if ($request->user()->tenant_id !== $tenant->id) {
            return $this->badRequestResponse('You are not authorized to see projects in this tenant');
        }
        $project = $this->projectRepository->getProjects($tenant->id);

        return $this->successResponse(data: [
            'projects' => ProjectResource::collection($project)
        ]);
    }

    public function create(Tenant $tenant, CreateProjectRequest $request): JsonResponse
    {
        $data = [
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status,
            'deadline' => $request->deadline,
        ];

        $project = $this->projectRepository->create($data);
        if ($request->user()->project_id !== null) {
            return $this->badRequestResponse('You are already part of a project');
        }

        $this->userRepository->update($request->user()->id, ['project_id' => $project->id]);

        return $this->successResponse('Project created successfully', [
            'project' => new ProjectResource($project)
        ]);
    }

    public function getSpecificProject(Request $request, Tenant $tenant, Project $project): JsonResponse
    {

        if ($request->user()->tenant_id !== $tenant->id) {
            return $this->badRequestResponse("You do not have permission to view projects in this tenant");
        }
        $project = $this->projectRepository->find($project->id);

        return $this->successResponse(data: [
            'project' => new ProjectResource($project)
        ]);
    }

    public function updateProject(Tenant $tenant, Project $project, UpdateProjectRequest $request): JsonResponse
    {
        $data = [
            'name' => $request->name ?? $project->name,
            'description' => $request->description ?? $project->description,
            'status' => $request->status ?? $project->status,
            'deadline' => $request->deadline ?? $project->deadline,
        ];

        $project = $this->projectRepository->find($project->id);

        $this->projectRepository->update($project->id, $data);

        return $this->successResponse('Project updated successfully', [
            'project' => new ProjectResource($project->refresh())
        ]);
    }

    public function delete(Tenant $tenant, Project $project): JsonResponse
    {
        $this->projectRepository->delete($project->id);

        return $this->successResponse('Project deleted successfully');
    }

    public function addUser(Tenant $tenant, Project $project, User $user): JsonResponse
    {
        if ($user->tenant_id !== $tenant->id) {
            return $this->badRequestResponse('User does not belong to this tenancy');
        }

        if ($user->project_id === $project->id) {
            return $this->badRequestResponse('User is already part of  this project');
        }

        if ($user->project_id !== null) {
            return $this->badRequestResponse('User is already part of another project');
        }

        $this->userRepository->update($user->id, ['project_id' => $project->id]);
        return $this->successResponse("User ,{$user->name} has been added to project ,{$project->name}", [
            'project' => new ProjectResource($project)
        ]);
    }

    public function assignRole(Tenant $tenant, Project $project, User $user): JsonResponse
    {
        if ($user->project_id !== $project->id) {
            return $this->badRequestResponse('User does not belong to this project');
        }

        $this->userRepository->update($user->id, ['role' => UserRolesEnum::PROJECT_MANAGER->value]);

        return $this->successResponse("User ,{$user->name} has been assigned the Project Manager role");
    }
}
