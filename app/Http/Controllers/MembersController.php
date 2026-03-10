<?php

namespace App\Http\Controllers;

use App\Models\User;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\MembersService;
use App\Http\Requests\SendInvitationRequest;


class MembersController extends Controller
{
    //
    public function __construct(private readonly MembersService $membersService){

    }

    public function getTenantMembers(Tenant $tenant):JsonResponse
    {
        return $this->membersService->getTenantMembers($tenant);
    }

    public function sendInvitation(Tenant $tenant, SendInvitationRequest $request):JsonResponse
    {
        return $this->membersService->sendInvitation($tenant,$request);
    }

    public function acceptInvitation(Request $request):JsonResponse
    {
        return $this->membersService->acceptInvitation($request);
    }

    public function removeMember(Tenant $tenant,Request $request, User $user):JsonResponse
    {
        return $this->membersService->removeMember($tenant,$request, $user);
    }
}
