<?php

namespace App\Support\Services;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\TenantResource;
use App\Support\Repositories\TenantRepository;
use App\Notifications\SendInvitationNotice;
use App\Http\Requests\SendInvitationRequest;
use App\Support\Repositories\UserRepository;
use Illuminate\Support\Facades\Notification;

class MembersService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly TenantRepository $tenantRepository)
    {
        //
    }

    public function getTenantMembers(Tenant $tenant):JsonResponse
    {
        $users=$this->userRepository->getAllByTenantId($tenant->id);

        return $this->successResponse(message:'Members fetched successfully',data:[
            'members'=>UserResource::collection($users)
        ]);
    }

    public function sendInvitation(Tenant $tenant, SendInvitationRequest $request):JsonResponse
    {
        $inviteCode=$this->generateInviteCode();
        $expiryTime=900;

        $tenant=$this->tenantRepository->find($tenant->id);
        
        Cache::put("TENANCY_INVITATION_CODE_$inviteCode",[$request->receiver_email,$tenant->id], $expiryTime);

        Notification::route('mail', $request->receiver_email)->notify(new SendInvitationNotice($inviteCode,$expiryTime,$tenant->name));
        
        return $this->successResponse("An invitation has been sent to {$request->receiver_email}");
    }

    public function acceptInvitation(Request $request):JsonResponse
    {
        $inviteCode=$request->query('token');

        $cacheInviteCode=Cache::get("TENANCY_INVITATION_CODE_$inviteCode");

        if($request->user()->tenant_id!== null) {
            return $this->badRequestResponse("You are already a member of a tenant", 400);
        }
        else if (!$cacheInviteCode || $cacheInviteCode[0] !== $request->user()->email) {
            return $this->badRequestResponse("Invalid or expired invitation code", 400);
        }

        $this->userRepository->update($request->user()->id,[
            'tenant_id'=> $cacheInviteCode[1]
        ]);

        $tenant= $this->tenantRepository->find($cacheInviteCode[1]);
        
        return $this->successResponse("You have successfully joined the tenant {$tenant->name}", data: [
            'tenant' => new TenantResource($tenant)

        ]);
    }

    public function removeMember(Tenant $tenant, Request $request, User $user):JsonResponse
    {
        $user=$this->userRepository->find($user->id);

        if(!$user || $user->tenant_id !== $tenant->id) {
            return $this->badRequestResponse("User not found in this tenant", 404);
        }

        if($user->id === $request->user()->id) {
            return $this->badRequestResponse("You cannot remove yourself from the tenant", 400);
        }

        $this->userRepository->update($user->id,[
            'tenant_id'=> null
        ]);

        $users=$this->userRepository->findAllByTenant($request->user()->tenant_id);

        return $this->successResponse("User {$user->name} has been removed from the tenant {$tenant->name}",
        data: [
            'users'=>UserResource::collection($users),
        ]);
    }
}
