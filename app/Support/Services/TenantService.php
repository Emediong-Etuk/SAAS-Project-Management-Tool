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
use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\TenantResource;
use App\Http\Requests\CompanyLogoRequest;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Notifications\SendInvitationNotice;
use App\Http\Requests\SendInvitationRequest;
use App\Support\Repositories\TaskRepository;
use App\Support\Repositories\UserRepository;
use Illuminate\Support\Facades\Notification;
use App\Notifications\CreateTenantInfoNotice;
use App\Notifications\DeleteTenantInfoNotice;
use App\Notifications\UpdateTenantInfoNotice;
use App\Support\Repositories\TenantRepository;
use App\Notifications\RemoveTenantMemberNotice;
use App\Support\Repositories\ProjectRepository;


class TenantService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly TenantRepository $tenantRepository,
        private readonly UserRepository $userRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly TaskRepository $taskRepository
    ) {
        //
    }


    public function dashboard(Request $request): JsonResponse
    {
        $project = $this->projectRepository->findByTenant($request->user()->tenant->id)?? null;
        $totalProjects = $this->projectRepository->countAllByTenant($request->user()->tenant->id) ?? null;
        $totalProjectMembers = $this->projectRepository->getProjectMembersCount($request->user()->tenant->id) ?? null;
        $projectCompletionRate=$project !== null ?$this->calculateProjectCompletionRate($project,$totalProjects,$request->user()->tenant->id):null;
        $projectProgress=$project !==null ? $this->calculateProjectProgress($project):null;

        $totalTasks = $this->taskRepository->countAllByTenant($request->user()->tenant->id);
        $completedTasks=$this->taskRepository->completedTasks($request->user()->tenant->id);
        $pendingTasks=$this->taskRepository->pendingTasks($request->user()->tenant->id);

        $tenant=$this->tenantRepository->find($request->user()->tenant_id);
        $currentSubscriptionPlan=$this->subscriptionPlan($request);
        $projectStatus=$project->status;

        return $this->successResponse(data:[
            'no_of_projects'=>$totalProjects,
            'no_of_project_members'=>$totalProjectMembers,
            'project_completion_rate'=>$projectCompletionRate,
            'project_progress'=>$projectProgress,
            'project_status'=>$projectStatus,
            'no_of_tasks'=>$totalTasks,
            'no_of_completed_tasks'=>$completedTasks,
            'no_of_pending_tasks'=>$pendingTasks,
            'tenant'=>$tenant,
            'currentSubscriptionPlan'=>$currentSubscriptionPlan
        ]);
    }

    public function uploadCompanyLogo(CompanyLogoRequest $request):JsonResponse
    {
        $path=$request->file('logo')->store('company_logo','public');

        $this->tenantRepository->update($request->user()->tenant_id,[
            'company_logo'=>config('filesystems.disks.public.url').'/'.$path
        ]);

        $tenant=$this->tenantRepository->find($request->user()->tenant_id);

        return $this->successResponse(message:'uploaded',data:[
            'tenant'=>new TenantResource($tenant)
        ]);
    }

    public function subscriptionPlan(Request $request):string
    {
        $user=$this->userRepository->find($request->user()->id);

        return $user->subscription_plan;
    }

    public function create(CreateTenantRequest $request): JsonResponse
    {
        if ($request->user()->tenant_id) {
            return $this->badRequestResponse(message: 'You already own a tenant');
        }

        $data = [
            'name' => $request->name,
            'plan'=>PlansEnum::from($request->user()->subscription_plan)
        ];

        $tenant = $this->tenantRepository->create($data);
        $this->userRepository->update($request->user()->id, ['tenant_id' => $tenant->id, 'role' => UserRolesEnum::TENANT_ADMIN->value]);

        $request->user()->notify(new CreateTenantInfoNotice($tenant->name));

        return $this->successResponse(data: [
            'tenant' => new TenantResource($tenant)
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        $data = [
            'name' => $request->name
        ];

        if ($request->user()->tenant_id !== $tenant->id) {
            return $this->badRequestResponse(message: 'You do not belong to this tenant');
        }

        $this->tenantRepository->update($tenant->id, $data);
        $tenant = $this->tenantRepository->find($tenant->id);

        $this->notifyAllMembers($tenant, 'has been updated', 'update');

        return $this->successResponse(data: [
            'tenant' => new TenantResource($tenant)
        ]);
    }

    public function delete(Request $request, Tenant $tenant): JsonResponse
    {
        $this->tenantRepository->delete($tenant->id);

        if ($request->user()->tenant_id !== $tenant->id) {
            return $this->badRequestResponse(message: "You do not belong to this tenant");
        }

        $this->notifyAllMembers($tenant, 'has been deleted', 'delete');

        return $this->successResponse('Tenant deleted successfully');
    }

    public function sendInvitation(Tenant $tenant, SendInvitationRequest $request): JsonResponse
    {
        if ($request->user()->tenant_id !== $tenant->id) {
            return $this->badRequestResponse("You are not authorized to send invitations for this tenant");
        }

        $inviteCode = $this->generateInviteCode();
        $expiryTime = 900;

        $tenant = $this->tenantRepository->find($tenant->id);

        Cache::put("TENANCY_INVITATION_CODE_$inviteCode", [$request->receiver_email, $tenant->id, $request->user()->id], $expiryTime);

        Notification::route('mail', $request->receiver_email)->notify(new SendInvitationNotice($inviteCode, $expiryTime, $tenant->name));

        return $this->successResponse("An invitation has been sent to {$request->receiver_email}");
    }

    public function removeMember(Tenant $tenant, Request $request, User $user): JsonResponse
    {
        $user = $this->userRepository->find($user->id);

        if (!$user || $user->tenant_id !== $tenant->id) {
            return $this->badRequestResponse("User not found in this tenant", 404);
        }

        if ($user->id === $request->user()->id) {
            return $this->badRequestResponse("You cannot remove yourself from the tenant", 400);
        }

        $this->userRepository->update($user->id, [
            'tenant_id' => null
        ]);

        $users = $this->userRepository->findAllByTenant($request->user()->tenant_id);

        $this->notifyAllMembers($tenant, 'has been removed from the tenant', 'remove_member');

        return $this->successResponse(
            "User {$user->name} has been removed from the tenant {$tenant->name}",
            data: [
                'users' => UserResource::collection($users),
            ]
        );
    }

    public function notifyAllMembers(Tenant $tenant, string $keyMessage, string $purpose)
    {
        $members = $this->userRepository->findAllByTenant($tenant->id);

        for ($i = 0; $i < count($members); $i++) {
            if ($purpose === 'update') {
                $members[$i]->notify(new UpdateTenantInfoNotice($tenant->name));
            }

            if ($purpose === 'delete') {
                $members[$i]->notify(new DeleteTenantInfoNotice($tenant->name));
            }

            if ($purpose === 'remove_member') {
                $members[$i]->notify(new RemoveTenantMemberNotice($tenant->name, $members[$i]->name));
            }
        }
    }

    public function calculateProjectCompletionRate(Project $project,int $totalProjects, string $tenant_id):int
    {
        $projects=$this->projectRepository->getProjects($tenant_id);
        $completedProjects=[];
        foreach($projects as $project){
            if($project->status===ProjectStatus::COMPLETED->value){
                $completedProjects[]=$project;
            }
        }

        return count($completedProjects)/$totalProjects * 100;
    }

    public function calculateProjectProgress(Project $project):int
    {
        $totalProjectTasks=$this->taskRepository->countAllForProject($project->id);
        $totalCompletedTasks=$this->taskRepository->getCompletedTasksForProject($project->id);

        return $totalCompletedTasks/$totalProjectTasks * 100;
    }
}
