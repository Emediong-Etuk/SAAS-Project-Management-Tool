<?php

namespace App\Support\Services;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Support\Services\BaseService;
use App\Http\Resources\TenantResource;
use App\Repositories\TenantRepository;
use App\Http\Requests\CreateTenantRequest;
use App\Support\Repositories\UserRepository;


class TenantService extends BaseService
{
    /**
     * Create a new class instance.
     */
    public function __construct(private readonly TenantRepository $tenantRepository, private readonly UserRepository $userRepository)
    {
        //
    }

    public function create(CreateTenantRequest $request):JsonResponse
    {
        $data=[
            'name'=>$request->name,
            'plan'=>$request->plan,
        ];

        $tenant=$this->tenantRepository->create($data);
        $this->userRepository->update($request->user()->id, ['tenant_id'=>$tenant->id]);

        return $this->successResponse(data: [
            'tenant'=>new TenantResource($tenant)
        ]);
    }
}
