<?php

namespace App\Support\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Enum\PlansEnum;
use App\Models\Project;
use App\Enum\ProjectStatus;
use App\Enum\UserRolesEnum;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Support\Services\BaseService;
use App\Http\Resources\ProjectResource;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Support\Repositories\UserRepository;
use App\Contracts\Interface\AWSChimeInterface;
use App\Support\Repositories\ProjectRepository;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Support\Repositories\NotificationRepository;

class ProjectService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly ProjectRepository $projectRepository, private readonly UserRepository $userRepository, private readonly NotificationRepository $notificationRepository, private readonly AWSChimeInterface $awsInterface)
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

        $this->notifyAllMembers($project, 'has been created', 'create');

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
            'status' => ProjectStatus::from($request->status) ?? $project->status,
            'deadline' => $request->deadline ?? $project->deadline,
        ];

        $project = $this->projectRepository->find($project->id);

        $this->projectRepository->update($project->id, $data);

        $this->notifyAllMembers($project, 'has been updated', 'update');

        return $this->successResponse('Project updated successfully', [
            'project' => new ProjectResource($project->refresh())
        ]);
    }

    public function getProjectStatusList(Tenant $tenant, Project $project): JsonResponse
    {
        $statusList = ProjectStatus::cases();
        $statusArray = array_map(fn($status) => $status->value, $statusList);

        return $this->successResponse(data: [
            'status_list' => $statusArray
        ]);
    }

    public function delete(Tenant $tenant, Project $project): JsonResponse
    {
        $this->projectRepository->delete($project->id);

        $this->notifyAllMembers($project, 'has been deleted', 'delete');

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

        $this->notificationRepository->create([
            'user_id' => $user->id,
            'message' => "You have been added to the project ,{$project->name}.",
        ]);

        $this->notifyAllMembers($project, 'has been added to the project', 'add_member');

        return $this->successResponse("User ,{$user->name} has been added to project ,{$project->name}", [
            'project' => new ProjectResource($project)
        ]);
    }

    public function removeUser(Tenant $tenant, Project $project, User $user): JsonResponse
    {
        if ($user->project_id !== $project->id) {
            return $this->badRequestResponse('User does not belong to this project');
        }

        $this->userRepository->update($user->id, ['project_id' => null]);

        $this->notificationRepository->create([
            'user_id' => $user->id,
            'message' => "You have been removed from the project ,{$project->name}.",
        ]);

        $this->notifyAllMembers($project, 'has been removed from the project', 'remove_member');

        return $this->successResponse("User ,{$user->name} has been removed from project ,{$project->name}", [
            'project' => new ProjectResource($project)
        ]);
    }

    public function assignRole(Tenant $tenant, Project $project, User $user): JsonResponse
    {
        if ($user->project_id !== $project->id) {
            return $this->badRequestResponse('User does not belong to this project');
        }

        if ($user->subscription_plan !== PlansEnum::Pro->value) {
            return $this->badRequestResponse('User must have pro subscription to be assigned as Project Manager');
        }

        $this->userRepository->update($user->id, ['role' => UserRolesEnum::PROJECT_MANAGER->value]);

        return $this->successResponse("User ,{$user->name} has been assigned the Project Manager role");
    }

    public function createMeeting(Request $request, Tenant $tenant, Project $project): JsonResponse
    {

        $createMeeting = $this->awsInterface->createMeeting($request, $project);

        $this->notificationRepository->create([
            'user_id' => $request->user()->id,
            'message' => "Meeting created for project ,{$project->name}.",
        ]);

        return $createMeeting;
    }

    public function joinMeeting(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->awsInterface->createAttendee($project, $request);
    }

    public function getMeeting(Tenant $tenant, Project $project): JsonResponse
    {
        return $this->awsInterface->getMeeting($project);
    }

    public function getAttendee(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        return $this->awsInterface->getAttendee($project, $request);
    }

    public function listAttendees(Tenant $tenant, Project $project): JsonResponse
    {
        return $this->awsInterface->listAttendees($project);
    }

    public function deleteMeeting(Request $request, Tenant $tenant, Project $project): JsonResponse
    {

        $deleteMeeting = $this->awsInterface->deleteMeeting($project);

        $this->notificationRepository->create([
            'user_id' => $request->user()->id,
            'message' => "Meeting ended for project ,{$project->name}.",
        ]);

        return $deleteMeeting;
    }

    public function deleteAttendee(Request $request, Tenant $tenant, Project $project): JsonResponse
    {
        $attendee = $this->awsInterface->deleteAttendee($project, $request);

        $user = $this->userRepository->findByRole($tenant->id, $project->id);

        $this->notificationRepository->create([
            'user_id' => $request->user()->id,
            'message' => "{$request->user()->name} left the meeting for project ,{$project->name}.",
        ]);

        return $attendee;
    }

    public function updateProjectStatus(UpdateProjectStatusRequest $request, Tenant $tenant, Project $project): JsonResponse
    {
        $this->projectRepository->update($project->id, ['status' => ProjectStatus::from($request->status)]);

        return $this->successResponse('Project status updated successfully', [
            'project' => new ProjectResource($project->refresh())
        ]);
    }

    public function notifyAllMembers(Project $project, string $keyMessage, string $purpose)
    {
        $members = $this->projectRepository->getProjectMembers($project->id);

        for ($i = 0; $i < count($members); $i++) {

            if ($purpose === 'create') {
                $this->notificationRepository->create([
                    'user_id' => $members[$i]->id,
                    'message' => "Project ,{$project->name} has been created.",
                ]);
            }


            if ($purpose === 'update') {
                $this->notificationRepository->create([
                    'user_id' => $members[$i]->id,
                    'message' => "Project ,{$project->name} has been updated.",
                ]);
            }

            if ($purpose === 'delete') {
                $this->notificationRepository->create([
                    'user_id' => $members[$i]->id,
                    'message' => "Project ,{$project->name} has been deleted.",
                ]);
            }

            if ($purpose === 'add_member') {
                $this->notificationRepository->create([
                    'user_id' => $members[$i]->id,
                    'message' => "{$members[$i]->name} has been added to the project ,{$project->name}.",
                ]);
            }

            if ($purpose === 'remove_member') {
                $this->notificationRepository->create([
                    'user_id' => $members[$i]->id,
                    'message' => "{$members[$i]->name} has been removed from the project ,{$project->name}.",
                ]);
            }
        }
    }
}
