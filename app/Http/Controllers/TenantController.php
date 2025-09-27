<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\CreateTenantRequest;
use App\Support\Services\TenantService;

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
}
