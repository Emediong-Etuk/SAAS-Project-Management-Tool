<?php

namespace App\Support\Services;

use App\Models\Tenant;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use App\Support\Services\BaseService;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Support\Repositories\ProjectRepository;

class ProjectService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly ProjectRepository $projectRepository)
    {
        //
    }

    public function getProjects(Tenant $tenant):JsonResponse
    {
        $project=$this->projectRepository->getProjects($tenant->id);

        return $this->successResponse(data: [
            'projects'=> ProjectResource::collection($project)
        ]);
    }

    public function create(Tenant $tenant, CreateProjectRequest $request):JsonResponse
    {
        $data=[
            'tenant_id'=>$tenant->id,
            'name'=>$request->name,
            'description'=>$request->description,
            'status'=>$request->status,
            'deadline'=>$request->deadline,
        ];

        $project=$this->projectRepository->create($data);

        return $this->successResponse('Project created successfully',[
            'project'=>new ProjectResource($project)
        ]);


    }

    public function getSpecificProject(Tenant $tenant, Project $project):JsonResponse
    {

        $project=$this->projectRepository->find($project->id);

        return $this->successResponse(data: [
            'project'=>new ProjectResource($project)
        ]);
    }

    public function updateProject(Tenant $tenant, Project $project, UpdateProjectRequest $request):JsonResponse
    {
        $data=[
            'name'=>$request->name ?? $project->name,
            'description'=>$request->description ?? $project->description,
            'status'=>$request->status ?? $project->status,
            'deadline'=>$request->deadline ?? $project->deadline,
        ];

        $project=$this->projectRepository->find($project->id);
        
        $this->projectRepository->update($project->id, $data);

        return $this->successResponse('Project updated successfully',[
            'project'=>new ProjectResource($project)
        ]);
    }

    public function delete(Tenant $tenant, Project $project):JsonResponse
    {
        $this->projectRepository->delete($project->id);

        return $this->successResponse('Project deleted successfully');
    }
}
