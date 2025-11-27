<?php

namespace App\Support\Services;

use App\Models\User;
use App\Models\Tenant;
use App\Enum\PlansEnum;
use App\Enum\UserRolesEnum;
use Illuminate\Http\JsonResponse;
use App\Support\Services\BaseService;
use App\Http\Resources\TenantResource;
use App\Http\Requests\CreateTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use App\Support\Repositories\UserRepository;
use App\Support\Repositories\TenantRepository;


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
        if($request->user()->tenant_id){
            return $this->badRequestResponse(message: 'You already own a tenant');
        }

        $data=[
            'name'=>$request->name,
            'plan'=>PlansEnum::Pro->value,
        ];

        $tenant=$this->tenantRepository->create($data);
        $this->userRepository->update($request->user()->id, ['tenant_id'=>$tenant->id, 'role'=> UserRolesEnum::TENANT_ADMIN->value]);

        return $this->successResponse(data: [
            'tenant'=>new TenantResource($tenant)
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant):JsonResponse
    {
        $data=[
            'name'=>$request->name
        ];

        if($request->user()->tenant_id !== $tenant->id){
            return $this->badRequestResponse(message: 'You do not currently own a tenant to edit, create one first');
        }


        $this->tenantRepository->update($tenant->id,$data);
        $tenant=$this->tenantRepository->find($tenant->id);
        
        return $this->successResponse(data: [
            'tenant'=>new TenantResource($tenant)
        ]);
    }

    public function delete(Tenant $tenant):JsonResponse
    {
        $this->tenantRepository->delete($tenant->id);

        return $this->successResponse('Tenant deleted successfully');
    }
    
}
