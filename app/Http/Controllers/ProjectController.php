<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
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
        return $this->projectService->create($tenant, $request);
    }

    public function getSpecificProject(Tenant $tenant, Project $project):JsonResponse
    {
        return $this->projectService->getSpecificProject($tenant, $project);
    }

    public function update(Tenant $tenant, Project $project, UpdateProjectRequest $request):JsonResponse
    {
        return $this->projectService->updateProject($tenant, $project, $request);
    }

    public function delete(Tenant $tenant, Project $project):JsonResponse
    {
        return $this->projectService->delete($tenant, $project);
    }
}
