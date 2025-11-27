<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use App\Support\Services\ProjectService;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;

class ProjectController extends Controller
{
    //

    public function __construct(private readonly ProjectService $projectService)
    {
        //
    }

    public function getProjects(Tenant $tenant):JsonResponse
    {
        return $this->projectService->getProjects($tenant);
    }

    public function create(Tenant $tenant, CreateProjectRequest $request):JsonResponse
    {
        Gate::authorize('create', Project::class);
        
        return $this->projectService->create($tenant, $request);
    }

    public function getSpecificProject(Tenant $tenant, Project $project):JsonResponse
    {
        return $this->projectService->getSpecificProject($tenant, $project);
    }

    public function update(Tenant $tenant, Project $project, UpdateProjectRequest $request):JsonResponse
    {
        if($request->user()->cannot('update', $project)){
            abort(404, "You do not have permission to update this project");
        }
        return $this->projectService->updateProject($tenant, $project, $request);
    }

    public function delete(Tenant $tenant, Project $project):JsonResponse
    {
        return $this->projectService->delete($tenant, $project);
    }

    public function addUser(Tenant $tenant, Project $project, User $user):JsonResponse
    {
        return $this->projectService->addUser($tenant, $project, $user);
    }

    public function assignRole(Tenant $tenant, Project $project, User $user):JsonResponse
    {
        return $this->projectService->assignRole($user);
    }
}
