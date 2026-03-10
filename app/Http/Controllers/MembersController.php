<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\MembersService;


class MembersController extends Controller
{
    //
    public function __construct(private readonly MembersService $membersService){

    }

    public function getTenantMembers(Tenant $tenant):JsonResponse
    {
        return $this->membersService->getTenantMembers($tenant);
    }

    public function acceptInvitation(Request $request):JsonResponse
    {
        return $this->membersService->acceptInvitation($request);
    }

    
}
