<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Models\Project;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Services\ProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    //

    public function __construct(private readonly ProjectService $projectService)
    {
        //
    }

    public function getProjects(Request $request, Tenant $tenant): JsonResponse
    {
        return $this->projectService->getProjects($request, $tenant);
    }

    public function create(Tenant $tenant, CreateProjectRequest $request): JsonResponse
    {
        Gate::authorize('create', Project::class);

        return $this->projectService->create($tenant, $request);
    }

    public function getSpecificProject(Tenant $tenant, Project $project, Request $request): JsonResponse
    {
        return $this->projectService->getSpecificProject($request, $tenant, $project);
    }

    public function update(Tenant $tenant, Project $project, UpdateProjectRequest $request): JsonResponse
    {

        return $this->projectService->updateProject($tenant, $project, $request);
    }

    public function projectStatusList(Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->getProjectStatusList($tenant, $project);
    }

    public function delete(Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->delete($tenant, $project);
    }

    public function addUser(Tenant $tenant, Project $project, User $user): JsonResponse
    {
        return $this->projectService->addUser($tenant, $project, $user);
    }

    public function removeUser(Tenant $tenant, Project $project, User $user): JsonResponse
    {
        return $this->projectService->removeUser($tenant, $project, $user);
    }

    public function assignRole(Tenant $tenant, Project $project, AssignRoleRequest $request): JsonResponse
    {
        return $this->projectService->assignRole($tenant, $project, $request);
    }

    public function createMeeting(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->createMeeting($request, $tenant, $project);
    }

    public function joinMeeting(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->joinMeeting($request, $tenant, $project);
    }

    public function getMeeting(Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->getMeeting($tenant, $project);
    }

    public function getAttendee(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->getAttendee($request, $tenant, $project);
    }

    public function listAttendees(Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->listAttendees($tenant, $project);
    }

    public function deleteMeeting(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->deleteMeeting($request, $tenant, $project);
    }

    public function deleteAttendee(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->projectService->deleteAttendee($request, $tenant, $project);
    }

    public function updateProjectStatus(Tenant $tenant, Project $project, UpdateProjectStatusRequest $request): JsonResponse
    {
        return $this->projectService->updateProjectStatus($request, $tenant, $project);
    }
}
