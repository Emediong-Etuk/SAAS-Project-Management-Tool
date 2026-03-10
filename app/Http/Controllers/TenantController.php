<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompanyLogoRequest;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\SendInvitationRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Services\TenantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TenantController extends Controller
{
    public function __construct(private readonly TenantService $tenantService) {}

    public function dashboard(Request $request): JsonResponse
    {
        return $this->tenantService->dashboard($request);
    }

    public function uploadCompanyLogo(CompanyLogoRequest $request): JsonResponse
    {
        return $this->tenantService->uploadCompanyLogo($request);
    }

    public function create(CreateTenantRequest $request): JsonResponse
    {
        return $this->tenantService->create($request);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): JsonResponse
    {
        return $this->tenantService->update($request, $tenant);
    }

    public function delete(Request $request, Tenant $tenant): JsonResponse
    {
        return $this->tenantService->delete($request, $tenant);
    }

    public function sendInvitation(Tenant $tenant, SendInvitationRequest $request): JsonResponse
    {
        return $this->tenantService->sendInvitation($tenant, $request);
    }

    public function removeMember(Tenant $tenant, Request $request, User $user): JsonResponse
    {
        return $this->tenantService->removeMember($tenant, $request, $user);
    }
}
