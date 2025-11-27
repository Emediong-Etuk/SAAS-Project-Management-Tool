<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Support\Services\TenantService;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\UpdateTenantRequest;

class TenantController extends Controller
{
    //
    public function __construct(private readonly TenantService $tenantService)
    {
        //
    }

    public function create(CreateTenantRequest $request):JsonResponse
    {
        return $this->tenantService->create($request);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant):JsonResponse
    {
        return $this->tenantService->update($request, $tenant);
    }
    
    public function delete(Tenant $tenant):JsonResponse
    {
        return $this->tenantService->delete($tenant);
    }
}
