<?php

namespace App\Support\Services;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Support\Services\BaseService;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\TenantResource;
use App\Support\Repositories\NotificationRepository;
use App\Support\Repositories\TenantRepository;
use App\Support\Repositories\UserRepository;

class MembersService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly UserRepository $userRepository, private readonly TenantRepository $tenantRepository, private readonly NotificationRepository $notificationRepository)
    {
        //
    }

    public function getTenantMembers(Tenant $tenant): JsonResponse
    {
        $users = $this->userRepository->findAllByTenant($tenant->id);

        return $this->successResponse(message: 'Members fetched successfully', data: [
            'members' => UserResource::collection($users)
        ]);
    }



    public function acceptInvitation(Request $request): JsonResponse
    {
        $inviteCode = $request->query('token');

        $cacheInviteCode = Cache::get("TENANCY_INVITATION_CODE_$inviteCode");

        if ($request->user()->tenant_id !== null) {
            return $this->badRequestResponse("You are already a member of a tenant", 400);
        }
        if (!$cacheInviteCode) {
            return $this->badRequestResponse("Invalid or expired invitation code", 400);
        }
        if ($cacheInviteCode[0] !== $request->user()->email) {
            return $this->badRequestResponse("Invalid or expired invitation code", 400);
        }

        $this->userRepository->update($request->user()->id, [
            'tenant_id' => $cacheInviteCode[1],
        ]);

        $this->notificationRepository->create([
            'user_id' => Cache::get("TENANCY_INVITATION_CODE_$inviteCode")[2],
            'message' => "{$request->user()->name} has accepted your invitation to join the tenant.",
        ]);

        $tenant = $this->tenantRepository->find($cacheInviteCode[1]);

        return $this->successResponse("You have successfully joined the tenant {$tenant->name}", data: [
            'tenant' => new TenantResource($tenant)

        ]);
    }
}
