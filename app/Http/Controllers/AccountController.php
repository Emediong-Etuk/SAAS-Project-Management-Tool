<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAccountRequest;
use App\Http\Requests\VerifyUpdatedEmail;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Services\AccountService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;


class AccountController extends Controller
{
    //
    public function __construct(
        private readonly AccountService $accountService
    ) {}

    public function view(Tenant $tenant, User $user,Request $request): JsonResponse
    {
        return $this->accountService->view($tenant,$user,$request);
    }

    public function update(Tenant $tenant, User $user,UpdateAccountRequest $request): JsonResponse
    {
        return $this->accountService->update($tenant, $user,$request);
    }

    public function verifyEmail(Tenant $tenant,User $user,VerifyUpdatedEmail $request): JsonResponse
    {
        return $this->accountService->verifyEmail($tenant, $user,$request);
    }

    public function delete(Tenant $tenant, User $user,Request $request): JsonResponse
    {
        return $this->accountService->delete($tenant,$user,$request);
    }
}
